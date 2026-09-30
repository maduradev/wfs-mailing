<?php

namespace App\Policies;

use App\Enums\LetterStatus;
use App\Enums\LetterType;
use App\Enums\UserRole;
use App\Models\Letter;
use App\Models\User;

class LetterPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::KARYAWAN,
            UserRole::PRODUCT_MANAGER,
            UserRole::HRD_MANAGER,
            UserRole::OA_OC_MANAGER,
        ], true);
    }

    public function createType(User $user, LetterType $type): bool
    {
        return $user->role === UserRole::HRD_MANAGER
            || ($user->role === UserRole::KARYAWAN && in_array($type, [LetterType::LEAVE, LetterType::MUTATION], true));
    }

    public function view(User $user, Letter $letter): bool
    {
        if ($user->role === UserRole::HRD_MANAGER) {
            return true;
        }

        if ($user->role === UserRole::KARYAWAN) {
            return $letter->requested_by === $user->id || $letter->subject_user_id === $user->id;
        }

        if ($user->role === UserRole::PRODUCT_MANAGER && $user->department_id !== null) {
            return $letter->requester()->where('department_id', $user->department_id)->exists()
                || $letter->subject()->where('department_id', $user->department_id)->exists();
        }

        return $user->role === UserRole::OA_OC_MANAGER
            && in_array($letter->status, [
                LetterStatus::APPROVED,
                LetterStatus::ISSUED,
                LetterStatus::COMPLETED,
                LetterStatus::ARCHIVED,
            ], true);
    }

    public function update(User $user, Letter $letter): bool
    {
        if ($letter->status !== LetterStatus::DRAFT) {
            return false;
        }

        return $user->role === UserRole::HRD_MANAGER
            || ($user->role === UserRole::KARYAWAN
                && $letter->requested_by === $user->id
                && in_array($letter->type, [LetterType::LEAVE, LetterType::MUTATION], true));
    }

    public function submit(User $user, Letter $letter): bool
    {
        return $this->update($user, $letter);
    }

    public function addAttachment(User $user, Letter $letter): bool
    {
        return $letter->status === LetterStatus::DRAFT && $this->update($user, $letter);
    }

    public function downloadAttachment(User $user, Letter $letter): bool
    {
        return $this->view($user, $letter);
    }

    public function previewPdf(User $user, Letter $letter): bool
    {
        return $this->view($user, $letter);
    }
}
