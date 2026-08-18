<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkPermit;
use App\Enums\AssignmentType;
use App\Enums\WorkPermitStatus;

/**
 * Context-based authorization policies for Work Permits.
 *
 * Visibilitas (view/read-only) bersifat global agar semua operator internal
 * dapat melihat pekerjaan yang sedang berjalan di plant demi keselamatan bersama.
 *
 * Tindakan (Edit, Submit, Approve, Release, Close) dibatasi secara ketat
 * berdasarkan context assignment user pada PTW tersebut.
 */
class WorkPermitPolicy
{
    /** Semua user internal yang terautentikasi dapat melihat detail PTW */
    public function view(User $user, WorkPermit $permit): bool
    {
        return true;
    }

    /** Hanya user yang ditugaskan sebagai requester yang boleh mengedit (Draft/Revision) */
    public function edit(User $user, WorkPermit $permit): bool
    {
        if (!$permit->getStatusEnum()->isEditable()) {
            return false;
        }

        return $permit->activeAssignments()
                      ->where('user_id', $user->id)
                      ->where('assignment_type', AssignmentType::REQUESTER->value)
                      ->exists();
    }

    public function editField(User $user, WorkPermit $permit, string $field): bool
    {
        return $this->edit($user, $permit);
    }

    public function submit(User $user, WorkPermit $permit): bool
    {
        if (!$permit->getStatusEnum()->isEditable()) {
            return false;
        }

        return $permit->activeAssignments()
                      ->where('user_id', $user->id)
                      ->where('assignment_type', AssignmentType::REQUESTER->value)
                      ->exists();
    }

    /** Hanya pending approver yang ditunjuk pada giliran aktif yang boleh menyetujui */
    public function approve(User $user, WorkPermit $permit): bool
    {
        if ($permit->getStatusEnum() !== WorkPermitStatus::APPROVAL) {
            return false;
        }

        $nextPending = $permit->nextPendingApproval();
        if (!$nextPending) {
            return false;
        }

        return $nextPending->approver_id === $user->id;
    }

    public function reject(User $user, WorkPermit $permit): bool
    {
        return $this->approve($user, $permit);
    }

    /** Hanya user yang ditugaskan sebagai release yang boleh melakukan release (dari status APPROVED) */
    public function release(User $user, WorkPermit $permit): bool
    {
        if ($permit->getStatusEnum() !== WorkPermitStatus::APPROVED) {
            return false;
        }

        return $permit->activeAssignments()
                      ->where('user_id', $user->id)
                      ->where('assignment_type', AssignmentType::RELEASE->value)
                      ->exists();
    }

    /** Hanya user yang ditugaskan sebagai release yang boleh mengaktifkan (dari status RELEASE) */
    public function activate(User $user, WorkPermit $permit): bool
    {
        if ($permit->getStatusEnum() !== WorkPermitStatus::RELEASE) {
            return false;
        }

        return $permit->activeAssignments()
                      ->where('user_id', $user->id)
                      ->where('assignment_type', AssignmentType::RELEASE->value)
                      ->exists();
    }

    /** Hanya requester yang boleh mengirim notifikasi pekerjaan selesai */
    public function finish(User $user, WorkPermit $permit): bool
    {
        if ($permit->getStatusEnum() !== WorkPermitStatus::ACTIVE) {
            return false;
        }

        return $permit->activeAssignments()
                      ->where('user_id', $user->id)
                      ->where('assignment_type', AssignmentType::REQUESTER->value)
                      ->exists();
    }

    /** Hanya release person yang boleh menutup/close PTW */
    public function close(User $user, WorkPermit $permit): bool
    {
        if ($permit->getStatusEnum() !== WorkPermitStatus::FINISH_NOTIFICATION) {
            return false;
        }

        return $permit->activeAssignments()
                      ->where('user_id', $user->id)
                      ->where('assignment_type', AssignmentType::RELEASE->value)
                      ->exists();
    }
}
