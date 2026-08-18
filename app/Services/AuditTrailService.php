<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Central audit trail service.
 * All audit entries are append-only — no updates, no deletes via application.
 */
class AuditTrailService
{
    public function log(
        string  $action,
        string  $recordType,
        ?int    $recordId   = null,
        ?User   $user       = null,
        ?string $fieldName  = null,
        mixed   $oldValue   = null,
        mixed   $newValue   = null,
        ?Request $request   = null,
    ): AuditLog {
        $req = $request ?? RequestFacade::instance();

        return AuditLog::create([
            "user_id"     => $user?->id ?? auth()->id(),
            "action"      => $action,
            "record_type" => $recordType,
            "record_id"   => $recordId,
            "field_name"  => $fieldName,
            "old_value"   => $oldValue !== null ? (is_string($oldValue) ? $oldValue : json_encode($oldValue)) : null,
            "new_value"   => $newValue !== null ? (is_string($newValue) ? $newValue : json_encode($newValue)) : null,
            "ip_address"  => $req->ip(),
            "user_agent"  => $req->userAgent(),
            "created_at"  => now(),
        ]);
    }
}
