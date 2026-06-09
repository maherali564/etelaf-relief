<?php

namespace App\Policies;

use App\Models\User;

class QuickActionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_quick_action');
    }

    public function view(User $user): bool
    {
        return $user->can('view_quick_action');
    }

    public function create(User $user): bool
    {
        return $user->can('create_quick_action');
    }

    public function update(User $user): bool
    {
        return $user->can('update_quick_action');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_quick_action');
    }
}
