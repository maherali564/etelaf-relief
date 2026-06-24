<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_role');
    }

    public function view(User $user): bool
    {
        return $user->can('view_role');
    }

    public function create(User $user): bool
    {
        return $user->can('create_role');
    }

    public function update(User $user): bool
    {
        return $user->can('update_role');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_role');
    }
}
