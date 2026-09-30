<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function updateProfile(User $user, User $target): bool
    {
        return $user->is($target);
    }

    public function viewSignature(User $user, User $target): bool
    {
        return $user->is($target);
    }

    public function manageUsers(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
