<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\WorkPermitStatus;
use App\Models\User;
use App\Models\WorkPermit;
use App\Models\WorkPermitApproval;
use App\Models\WorkPermitStatusHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * All PTW workflow transitions go through this service.
 * Never change work_permits.status directly in a controller.
 */
class WorkPermitWorkflowService
{
    public function __construct(
        private readonly AuditTrailService $audit,
    ) {}

    public function submit(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        $this->transition($permit, $actor, WorkPermitStatus::SUBMITTED, $comment);
    }

    public function sendToApproval(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        DB::transaction(function () use ($permit, $actor, $comment) {
            $this->guardTransition($permit, WorkPermitStatus::APPROVAL);
            $this->buildApprovalRecords($permit);
            $this->applyTransition($permit, $actor, WorkPermitStatus::APPROVAL, $comment);
        });
    }

    public function approve(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        DB::transaction(function () use ($permit, $actor, $comment) {
            $approval = $this->findPendingApprovalForActor($permit, $actor);

            $approval->update([
                'status'      => ApprovalStatus::APPROVED,
                'comment'     => $comment,
                'approved_at' => now(),
            ]);

            $this->audit->log('APPROVE', 'WorkPermitApproval', $approval->id);

            $cycle   = $permit->currentCycleNumber();
            $pending = WorkPermitApproval::where('work_permit_id', $permit->id)
                                         ->where('cycle_number', $cycle)
                                         ->where('status', ApprovalStatus::PENDING->value)
                                         ->exists();

            if (!$pending) {
                $this->applyTransition($permit, $actor, WorkPermitStatus::APPROVED, $comment);
            }
        });
    }

    public function reject(WorkPermit $permit, User $actor, string $comment): void
    {
        DB::transaction(function () use ($permit, $actor, $comment) {
            $approval = $this->findPendingApprovalForActor($permit, $actor);

            $approval->update([
                'status'      => ApprovalStatus::REJECTED,
                'comment'     => $comment,
                'rejected_at' => now(),
            ]);

            $this->audit->log('REJECT', 'WorkPermitApproval', $approval->id);
            $this->applyTransition($permit, $actor, WorkPermitStatus::REJECTED, $comment);
        });
    }

    public function revise(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        $this->transition($permit, $actor, WorkPermitStatus::REVISION, $comment);
    }

    public function release(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        $this->transition($permit, $actor, WorkPermitStatus::RELEASE, $comment);
    }

    public function activate(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        $this->transition($permit, $actor, WorkPermitStatus::ACTIVE, $comment);
    }

    public function finish(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        $this->transition($permit, $actor, WorkPermitStatus::FINISH_NOTIFICATION, $comment);
    }

    public function close(WorkPermit $permit, User $actor, ?string $comment = null): void
    {
        $this->transition($permit, $actor, WorkPermitStatus::CLOSED, $comment);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function transition(WorkPermit $permit, User $actor, WorkPermitStatus $to, ?string $comment): void
    {
        DB::transaction(function () use ($permit, $actor, $to, $comment) {
            $this->guardTransition($permit, $to);
            $this->applyTransition($permit, $actor, $to, $comment);
        });
    }

    private function guardTransition(WorkPermit $permit, WorkPermitStatus $to): void
    {
        $current = $permit->getStatusEnum();

        if (!$current->canTransitionTo($to)) {
            throw new RuntimeException(
                'Invalid workflow transition: ' . $current->value . ' -> ' . $to->value
            );
        }
    }

    private function applyTransition(WorkPermit $permit, User $actor, WorkPermitStatus $to, ?string $comment): void
    {
        $from = $permit->getStatusEnum();

        $permit->update([
            'status'     => $to->value,
            'updated_by' => $actor->id,
        ]);

        WorkPermitStatusHistory::create([
            'work_permit_id' => $permit->id,
            'from_status'    => $from->value,
            'to_status'      => $to->value,
            'changed_by'     => $actor->id,
            'comment'        => $comment,
            'created_at'     => now(),
        ]);

        $this->audit->log(
            action:     'STATUS_CHANGE',
            recordType: 'WorkPermit',
            recordId:   $permit->id,
            fieldName:  'status',
            oldValue:   $from->value,
            newValue:   $to->value,
        );
    }

    private function buildApprovalRecords(WorkPermit $permit): void
    {
        $newCycle = ($permit->approvals()->max('cycle_number') ?? 0) + 1;

        $approvers = $permit->activeAssignments()
                            ->where('assignment_type', 'approver')
                            ->orderBy('approval_order')
                            ->get();

        if ($approvers->isEmpty()) {
            throw new RuntimeException(
                'Cannot send to approval: no approvers assigned to PTW #' . $permit->permit_number
            );
        }

        foreach ($approvers as $assignment) {
            WorkPermitApproval::create([
                'work_permit_id' => $permit->id,
                'approver_id'    => $assignment->user_id,
                'approval_order' => $assignment->approval_order,
                'cycle_number'   => $newCycle,
                'status'         => ApprovalStatus::PENDING->value,
            ]);
        }

        $this->audit->log('APPROVAL_CYCLE_CREATED', 'WorkPermit', $permit->id,
            newValue: 'cycle_' . $newCycle);
    }

    private function findPendingApprovalForActor(WorkPermit $permit, User $actor): WorkPermitApproval
    {
        $cycle = $permit->currentCycleNumber();

        $approval = WorkPermitApproval::where('work_permit_id', $permit->id)
            ->where('cycle_number', $cycle)
            ->where('approver_id', $actor->id)
            ->where('status', ApprovalStatus::PENDING->value)
            ->first();

        if (!$approval) {
            throw new RuntimeException(
                'User ' . $actor->name . ' does not have a pending approval record for this permit.'
            );
        }

        return $approval;
    }
}
