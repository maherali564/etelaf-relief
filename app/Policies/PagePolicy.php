<?php

namespace App\Policies;

use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_page');
    }

    public function view(User $user): bool
    {
        return $user->can('view_page');
    }

    public function create(User $user): bool
    {
        return $user->can('create_page');
    }

    public function update(User $user): bool
    {
        return $user->can('update_page');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_page');
    }
}
