<?php

use App\Http\Controllers\Auth\DevelopmentLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkPermitController;
use App\Http\Controllers\AttachmentController;
use App\Http\Middleware\BlockDevelopmentAuth;
use Illuminate\Support\Facades\Route;

// ── Development Authentication (blocked in production) ────────────────────────
Route::middleware([BlockDevelopmentAuth::class])->group(function () {
    Route::get('/dev-login',  [DevelopmentLoginController::class, 'show'])->name('auth.dev-login');
    Route::post('/dev-login', [DevelopmentLoginController::class, 'login'])->name('auth.dev-login.post');
});

Route::post('/logout', [DevelopmentLoginController::class, 'logout'])
     ->middleware('auth')->name('auth.logout');

// ── Protected Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Work Permits ──────────────────────────────────────────────────────────
    Route::resource('permits', WorkPermitController::class);

    // Workflow actions
    Route::post('permits/{permit}/submit',          [WorkPermitController::class, 'submit'])->name('permits.submit');
    Route::post('permits/{permit}/send-to-approval',[WorkPermitController::class, 'sendToApproval'])->name('permits.send-to-approval');
    Route::post('permits/{permit}/approve',         [WorkPermitController::class, 'approve'])->name('permits.approve');
    Route::post('permits/{permit}/reject',          [WorkPermitController::class, 'reject'])->name('permits.reject');
    Route::post('permits/{permit}/revise',          [WorkPermitController::class, 'revise'])->name('permits.revise');
    Route::post('permits/{permit}/release',         [WorkPermitController::class, 'release'])->name('permits.release');
    Route::post('permits/{permit}/activate',        [WorkPermitController::class, 'activate'])->name('permits.activate');
    Route::post('permits/{permit}/finish',          [WorkPermitController::class, 'finish'])->name('permits.finish');
    Route::post('permits/{permit}/close',           [WorkPermitController::class, 'close'])->name('permits.close');

    // Assignment
    Route::post('permits/{permit}/assign',                         [WorkPermitController::class, 'assignUser'])->name('permits.assign');
    Route::delete('permits/{permit}/assignments/{assignment}',     [WorkPermitController::class, 'unassignUser'])->name('permits.unassign');

    // Attachments
    Route::post('permits/{permit}/attachments',        [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('attachments/{attachment}/download',    [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}',          [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // My Approvals
    Route::get('/approvals', function () {
        $user = auth()->user();
        $approvals = \App\Models\WorkPermitApproval::with(['workPermit.plant', 'workPermit.building'])
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
        return view('approvals.index', compact('approvals'));
    })->name('approvals.index');

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', fn() => 'Phase 8 — User management coming soon')->name('users.index');
        Route::get('/audit', fn() => 'Phase 7 — Audit log coming soon')->name('audit.index');
    });

});

