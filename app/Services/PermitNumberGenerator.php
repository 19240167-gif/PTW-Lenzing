<?php

namespace App\Services;

use App\Models\PermitNumberSequence;
use Illuminate\Support\Facades\DB;

/**
 * Generates unique, race-condition-safe permit numbers.
 *
 * Uses SELECT FOR UPDATE inside a transaction to atomically increment
 * the sequence counter. Never uses COUNT()+1 or similar approaches.
 *
 * Format: PTW-{YEAR}-{000000}  e.g. PTW-2026-000001
 *
 * Scope: NEEDS CONFIRMATION — currently per-year global.
 * If company requires per-year+building scope, change buildScopeKey().
 */
class PermitNumberGenerator
{
    public function next(string $scopeKey = null): string
    {
        $scopeKey ??= $this->buildScopeKey();

        $number = DB::transaction(function () use ($scopeKey) {
            // Lock the row to prevent race conditions
            $sequence = PermitNumberSequence::lockForUpdate()
                ->where("scope_key", $scopeKey)
                ->first();

            if (!$sequence) {
                $sequence = PermitNumberSequence::create([
                    "scope_key"   => $scopeKey,
                    "last_number" => 0,
                ]);
            }

            $sequence->increment("last_number");
            return $sequence->fresh()->last_number;
        });

        return sprintf("PTW-%s-%06d", $scopeKey, $number);
    }

    private function buildScopeKey(): string
    {
        // NEEDS CONFIRMATION: per-year global (current) or per-year+building?
        return now()->format("Y");
    }
}
