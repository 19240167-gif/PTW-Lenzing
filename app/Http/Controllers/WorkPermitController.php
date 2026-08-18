<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentType;
use App\Enums\WorkPermitStatus;
use App\Http\Requests\StoreWorkPermitRequest;
use App\Http\Requests\UpdateWorkPermitRequest;
use App\Models\Building;
use App\Models\PermitType;
use App\Models\Plant;
use App\Models\User;
use App\Models\WorkPermit;
use App\Models\WorkPermitAssignment;
use App\Services\AuditTrailService;
use App\Services\PermitNumberGenerator;
use App\Services\WorkPermitWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkPermitController extends Controller
{
    public function __construct(
        private readonly WorkPermitWorkflowService $workflow,
        private readonly PermitNumberGenerator     $generator,
        private readonly AuditTrailService         $audit,
    ) {}

    public function index(Request $request)
    {
        $query = WorkPermit::with(['plant', 'building', 'requester', 'permitType'])
            ->withCount('approvals');

        // VISIBILITY: Semua user internal yang terautentikasi dapat melihat seluruh daftar PTW.
        // Tidak ada pembatasan query whereIn() berdasarkan assignment.

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('permit_number', 'like', $search)
                  ->orWhere('title', 'like', $search);
            });
        }

        $permits = $query->latest()->paginate(20)->withQueryString();
        $plants  = Plant::where('is_active', true)->orderBy('name')->get();
        $statuses = WorkPermitStatus::cases();

        return view('permits.index', compact('permits', 'plants', 'statuses'));
    }

    public function create()
    {
        $plants      = Plant::where('is_active', true)->orderBy('name')->get();
        $buildings   = Building::where('is_active', true)->orderBy('name')->get();
        $permitTypes = PermitType::where('is_active', true)->orderBy('name')->get();

        return view('permits.create', compact('plants', 'buildings', 'permitTypes'));
    }

    public function store(StoreWorkPermitRequest $request)
    {
        $user = $request->user();

        $permit = DB::transaction(function () use ($request, $user) {
            $permitNumber = $this->generator->next();

            $permit = WorkPermit::create([
                'permit_number'  => $permitNumber,
                'title'          => $request->title,
                'description'    => $request->description,
                'permit_type_id' => $request->permit_type_id,
                'plant_id'       => $request->plant_id,
                'building_id'    => $request->building_id,
                'requester_id'   => $user->id,
                'status'         => WorkPermitStatus::DRAFT->value,
                'valid_from'     => $request->valid_from,
                'valid_until'    => $request->valid_until,
                'created_by'     => $user->id,
                'updated_by'     => $user->id,
            ]);

            WorkPermitAssignment::create([
                'work_permit_id' => $permit->id,
                'user_id'        => $user->id,
                'assignment_type'=> AssignmentType::REQUESTER->value,
                'is_active'      => true,
                'assigned_by'    => $user->id,
                'assigned_at'    => now(),
            ]);

            $this->audit->log('CREATE_PERMIT', 'WorkPermit', $permit->id, newValue: $permitNumber);

            return $permit;
        });

        return redirect()
            ->route('permits.show', $permit)
            ->with('success', 'Work Permit ' . $permit->permit_number . ' berhasil dibuat.');
    }

    public function show(WorkPermit $permit, Request $request)
    {
        $this->authorize('view', $permit);

        $permit->load([
            'plant', 'building', 'permitType', 'requester',
            'activeAssignments.user',
            'approvals.approver',
            'attachments.uploadedBy',
            'statusHistories.changedBy',
        ]);

        $user         = $request->user();
        $canEdit      = $request->user()->can('edit', $permit);
        $canSubmit    = $request->user()->can('submit', $permit);
        $canApprove   = $request->user()->can('approve', $permit);
        $canReject    = $request->user()->can('reject', $permit);
        $canRelease   = $request->user()->can('release', $permit);
        $canActivate  = $request->user()->can('activate', $permit);
        $canFinish    = $request->user()->can('finish', $permit);
        $canClose     = $request->user()->can('close', $permit);

        $availableUsers = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'username', 'domain', 'position']);

        return view('permits.show', compact(
            'permit', 'user',
            'canEdit', 'canSubmit', 'canApprove', 'canReject',
            'canRelease', 'canActivate', 'canFinish', 'canClose',
            'availableUsers',
        ));
    }

    public function edit(WorkPermit $permit)
    {
        $this->authorize('edit', $permit);

        $permit->load(['plant', 'building', 'permitType']);
        $plants      = Plant::where('is_active', true)->orderBy('name')->get();
        $buildings   = Building::where('is_active', true)->orderBy('name')->get();
        $permitTypes = PermitType::where('is_active', true)->orderBy('name')->get();

        return view('permits.edit', compact('permit', 'plants', 'buildings', 'permitTypes'));
    }

    public function update(UpdateWorkPermitRequest $request, WorkPermit $permit)
    {
        $this->authorize('edit', $permit);

        $user = $request->user();

        DB::transaction(function () use ($request, $permit, $user) {
            $old = $permit->only(['title', 'description', 'plant_id', 'building_id', 'permit_type_id', 'valid_from', 'valid_until']);

            $permit->update([
                'title'          => $request->title,
                'description'    => $request->description,
                'permit_type_id' => $request->permit_type_id,
                'plant_id'       => $request->plant_id,
                'building_id'    => $request->building_id,
                'valid_from'     => $request->valid_from,
                'valid_until'    => $request->valid_until,
                'updated_by'     => $user->id,
            ]);

            $new = $permit->only(['title', 'description', 'plant_id', 'building_id', 'permit_type_id', 'valid_from', 'valid_until']);
            foreach ($new as $field => $newVal) {
                if ((string)($old[$field] ?? '') !== (string)($newVal ?? '')) {
                    $this->audit->log('UPDATE_PERMIT', 'WorkPermit', $permit->id,
                        fieldName: $field, oldValue: $old[$field], newValue: $newVal);
                }
            }
        });

        return redirect()
            ->route('permits.show', $permit)
            ->with('success', 'Work Permit berhasil diperbarui.');
    }

    public function destroy(WorkPermit $permit, Request $request)
    {
        $this->authorize('edit', $permit);

        if ($permit->getStatusEnum() !== WorkPermitStatus::DRAFT) {
            return back()->with('error', 'Hanya Work Permit berstatus Draft yang dapat dihapus.');
        }

        $permit->delete();
        $this->audit->log('DELETE_PERMIT', 'WorkPermit', $permit->id);

        return redirect()->route('permits.index')
            ->with('success', 'Work Permit ' . $permit->permit_number . ' dihapus.');
    }

    public function submit(WorkPermit $permit, Request $request)
    {
        $this->authorize('submit', $permit);
        $this->workflow->submit($permit, $request->user(), $request->comment);
        return back()->with('success', 'Work Permit berhasil di-submit.');
    }

    public function sendToApproval(WorkPermit $permit, Request $request)
    {
        abort_unless($request->user()->is_global_admin, 403);
        try {
            $this->workflow->sendToApproval($permit, $request->user());
            return back()->with('success', 'Work Permit dikirim ke tahap Approval.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(WorkPermit $permit, Request $request)
    {
        $this->authorize('approve', $permit);
        $request->validate(['comment' => ['nullable', 'string', 'max:1000']]);
        try {
            $this->workflow->approve($permit, $request->user(), $request->comment);
            return back()->with('success', 'Work Permit berhasil di-approve.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(WorkPermit $permit, Request $request)
    {
        $this->authorize('reject', $permit);
        $request->validate(['comment' => ['required', 'string', 'max:1000']]);
        try {
            $this->workflow->reject($permit, $request->user(), $request->comment);
            return back()->with('success', 'Work Permit ditolak.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revise(WorkPermit $permit, Request $request)
    {
        $this->workflow->revise($permit, $request->user(), $request->comment);
        return back()->with('success', 'Work Permit dikembalikan untuk revisi.');
    }

    public function release(WorkPermit $permit, Request $request)
    {
        $this->authorize('release', $permit);
        $request->validate(['comment' => ['nullable', 'string', 'max:1000']]);
        $this->workflow->release($permit, $request->user(), $request->comment);
        return back()->with('success', 'Work Permit berhasil di-release.');
    }

    public function activate(WorkPermit $permit, Request $request)
    {
        $this->authorize('activate', $permit);
        $this->workflow->activate($permit, $request->user());
        return back()->with('success', 'Work Permit diaktifkan.');
    }

    public function finish(WorkPermit $permit, Request $request)
    {
        $this->authorize('finish', $permit);
        $request->validate(['comment' => ['nullable', 'string', 'max:1000']]);
        $this->workflow->finish($permit, $request->user(), $request->comment);
        return back()->with('success', 'Finish Notification terkirim.');
    }

    public function close(WorkPermit $permit, Request $request)
    {
        $this->authorize('close', $permit);
        $this->workflow->close($permit, $request->user(), $request->comment);
        return back()->with('success', 'Work Permit ditutup.');
    }

    public function assignUser(WorkPermit $permit, Request $request)
    {
        abort_unless($request->user()->is_global_admin || $request->user()->can('edit', $permit), 403);

        $request->validate([
            'user_id'         => ['required', 'integer', 'exists:users,id'],
            'assignment_type' => ['required', 'string', 'in:approver,release'],
            'approval_order'  => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $permit) {
            WorkPermitAssignment::create([
                'work_permit_id'  => $permit->id,
                'user_id'         => $request->user_id,
                'assignment_type' => $request->assignment_type,
                'approval_order'  => $request->approval_order,
                'is_active'       => true,
                'assigned_by'     => $request->user()->id,
                'assigned_at'     => now(),
            ]);

            $this->audit->log('ASSIGN_USER', 'WorkPermit', $permit->id,
                fieldName: $request->assignment_type,
                newValue:  $request->user_id);
        });

        return back()->with('success', 'User berhasil ditugaskan.');
    }

    public function unassignUser(WorkPermit $permit, WorkPermitAssignment $assignment, Request $request)
    {
        abort_unless($request->user()->is_global_admin || $request->user()->can('edit', $permit), 403);
        abort_unless($assignment->work_permit_id === $permit->id, 404);

        $assignment->update(['is_active' => false, 'unassigned_at' => now()]);
        $this->audit->log('UNASSIGN_USER', 'WorkPermit', $permit->id,
            fieldName: $assignment->assignment_type->value,
            oldValue:  $assignment->user_id);

        return back()->with('success', 'Penugasan dibatalkan.');
    }
}
