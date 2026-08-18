<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPermitApproval extends Model
{
    protected $fillable = [
        'work_permit_id',
        'approver_id',
        'approval_order',
        'cycle_number',
        'status',
        'comment',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'status'      => ApprovalStatus::class,
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function workPermit(): BelongsTo
    {
        return $this->belongsTo(WorkPermit::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isPending(): bool
    {
        return $this->status === ApprovalStatus::PENDING;
    }
}
