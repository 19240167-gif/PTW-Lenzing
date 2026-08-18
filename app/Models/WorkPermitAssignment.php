<?php

namespace App\Models;

use App\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPermitAssignment extends Model
{
    protected $fillable = [
        'work_permit_id', 'user_id', 'assignment_type', 'approval_order',
        'is_active', 'assigned_by', 'assigned_at', 'unassigned_at',
    ];

    protected $casts = [
        'assignment_type' => AssignmentType::class,
        'is_active'       => 'boolean',
        'assigned_at'     => 'datetime',
        'unassigned_at'   => 'datetime',
    ];

    public function workPermit(): BelongsTo { return $this->belongsTo(WorkPermit::class); }
    public function user(): BelongsTo       { return $this->belongsTo(User::class); }
    public function assignedBy(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }
}
