<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStatus;
use App\Enums\AssignmentType;
use App\Enums\WorkPermitStatus;
use App\Models\WorkPermit;
use App\Models\WorkPermitApproval;
use App\Models\WorkPermitAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $myPermitIds = WorkPermitAssignment::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('work_permit_id');

        $stats = [
            'my_permits' => WorkPermit::whereIn('id', $myPermitIds)->count(),

            'pending_approval' => WorkPermitApproval::where('approver_id', $user->id)
                ->where('status', ApprovalStatus::PENDING->value)
                ->count(),

            'active_permits' => WorkPermit::whereIn('id', $myPermitIds)
                ->where('status', WorkPermitStatus::ACTIVE->value)
                ->count(),

            'my_releases' => WorkPermitAssignment::where('user_id', $user->id)
                ->where('assignment_type', AssignmentType::RELEASE->value)
                ->where('is_active', true)
                ->count(),
        ];

        $recentPermits = WorkPermit::with(['plant', 'building'])
            ->whereIn('id', $myPermitIds)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('user', 'stats', 'recentPermits'));
    }
}
