<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPermitAttachment extends Model
{
    protected $fillable = [
        "work_permit_id",
        "uploaded_by",
        "file_name",
        "file_path",
        "mime_type",
        "file_size",
        "attachment_type",
    ];

    public function workPermit(): BelongsTo
    {
        return $this->belongsTo(WorkPermit::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "uploaded_by");
    }

    /** Human-readable file size */
    public function formattedSize(): string
    {
        if (!$this->file_size) {
            return "—";
        }
        $units = ["B", "KB", "MB", "GB"];
        $i = floor(log($this->file_size, 1024));
        return round($this->file_size / pow(1024, $i), 1) . " " . $units[$i];
    }
}
