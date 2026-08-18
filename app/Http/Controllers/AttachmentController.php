<?php

namespace App\Http\Controllers;

use App\Models\WorkPermit;
use App\Models\WorkPermitAttachment;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function __construct(private readonly AuditTrailService $audit) {}

    public function store(WorkPermit $permit, Request $request)
    {
        $this->authorize('view', $permit);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20 MB max — NEEDS CONFIRMATION
        ]);

        $file     = $request->file('file');
        $original = $file->getClientOriginalName();
        $stored   = 'ptw/' . $permit->id . '/' . Str::uuid() . '_' . $original;

        // Store in non-public disk (private)
        Storage::disk('local')->put($stored, file_get_contents($file->getRealPath()));

        $attachment = WorkPermitAttachment::create([
            'work_permit_id' => $permit->id,
            'uploaded_by'    => $request->user()->id,
            'file_name'      => $original,
            'file_path'      => $stored,
            'mime_type'      => $file->getMimeType(),
            'file_size'      => $file->getSize(),
        ]);

        $this->audit->log('UPLOAD_ATTACHMENT', 'WorkPermitAttachment', $attachment->id,
            newValue: $original);

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function download(WorkPermitAttachment $attachment, Request $request)
    {
        $this->authorize('view', $attachment->workPermit);

        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $this->audit->log('DOWNLOAD_ATTACHMENT', 'WorkPermitAttachment', $attachment->id);

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(WorkPermitAttachment $attachment, Request $request)
    {
        $this->authorize('edit', $attachment->workPermit);

        Storage::disk('local')->delete($attachment->file_path);
        $this->audit->log('DELETE_ATTACHMENT', 'WorkPermitAttachment', $attachment->id,
            oldValue: $attachment->file_name);

        $attachment->delete();

        return back()->with('success', 'Attachment dihapus.');
    }
}
