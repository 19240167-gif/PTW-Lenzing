<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPermitStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'work_permit_id', 'from_status', 'to_status', 'changed_by', 'comment', 'created_at',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function workPermit(): BelongsTo { return $this->belongsTo(WorkPermit::class); }
    public function changedBy(): BelongsTo  { return $this->belongsTo(User::class, 'changed_by'); }
}
