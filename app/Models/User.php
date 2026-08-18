<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = [
        'employee_id', 'domain', 'username', 'upn', 'email', 'name',
        'department_id', 'position', 'is_active', 'is_global_admin',
    ];

    protected $hidden = ['remember_token'];

    protected $casts = [
        'is_active'       => 'boolean',
        'is_global_admin' => 'boolean',
    ];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function workPermits(): HasMany   { return $this->hasMany(WorkPermit::class, 'requester_id'); }
    public function assignments(): HasMany   { return $this->hasMany(WorkPermitAssignment::class); }
    public function approvals(): HasMany     { return $this->hasMany(WorkPermitApproval::class, 'approver_id'); }

    public function windowsIdentity(): string
    {
        if ($this->domain && $this->username) {
            return strtoupper($this->domain) . '\\' . $this->username;
        }
        return $this->upn ?? $this->email ?? $this->name;
    }
}
