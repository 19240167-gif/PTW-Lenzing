<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = ["plant_id", "name", "code", "is_active"];

    protected function casts(): array
    {
        return ["is_active" => "boolean"];
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function workPermits(): HasMany
    {
        return $this->hasMany(WorkPermit::class);
    }
}
