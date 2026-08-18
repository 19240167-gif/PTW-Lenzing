<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    protected $fillable = ["name", "code", "is_active"];

    protected function casts(): array
    {
        return ["is_active" => "boolean"];
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function workPermits(): HasMany
    {
        return $this->hasMany(WorkPermit::class);
    }
}
