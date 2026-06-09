<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_permission');
    }

    public function view(User $user): bool
    {
        return $user->can('view_permission');
    }

    public function create(User $user): bool
    {
        return $user->can('create_permission');
    }

    public function update(User $user): bool
    {
        return $user->can('update_permission');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_permission');
    }
}
