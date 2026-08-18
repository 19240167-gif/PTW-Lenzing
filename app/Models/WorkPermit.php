<?php

namespace App\Models;

use App\Enums\WorkPermitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPermit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'permit_number', 'title', 'description',
        'permit_type_id', 'plant_id', 'building_id', 'requester_id',
        'status', 'valid_from', 'valid_until', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'status'      => WorkPermitStatus::class,
        'valid_from'  => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function permitType(): BelongsTo  { return $this->belongsTo(PermitType::class); }
    public function plant(): BelongsTo       { return $this->belongsTo(Plant::class); }
    public function building(): BelongsTo    { return $this->belongsTo(Building::class); }
    public function requester(): BelongsTo   { return $this->belongsTo(User::class, 'requester_id'); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy(): BelongsTo   { return $this->belongsTo(User::class, 'updated_by'); }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkPermitAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(WorkPermitAssignment::class)->where('is_active', true);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkPermitApproval::class)->orderBy('approval_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(WorkPermitAttachment::class)->latest();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(WorkPermitStatusHistory::class)->oldest();
    }

    public function currentCycleNumber(): int
    {
        return $this->approvals()->max('cycle_number') ?? 1;
    }

    public function currentCycleApprovals(): HasMany
    {
        $cycle = $this->currentCycleNumber();
        return $this->hasMany(WorkPermitApproval::class)
                    ->where('cycle_number', $cycle)
                    ->orderBy('approval_order');
    }

    public function nextPendingApproval(): ?WorkPermitApproval
    {
        return $this->currentCycleApprovals()->where('status', 'pending')->first();
    }

    /** Always returns WorkPermitStatus enum regardless of hydration state */
    public function getStatusEnum(): WorkPermitStatus
    {
        $status = $this->getRawOriginal('status') ?? $this->attributes['status'] ?? null;
        if ($status instanceof WorkPermitStatus) {
            return $status;
        }
        return WorkPermitStatus::from((string) $status);
    }

    public function isEditable(): bool
    {
        return $this->getStatusEnum()->isEditable();
    }
}
