<?php

namespace App\Policies;

use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_post');
    }

    public function view(User $user): bool
    {
        return $user->can('view_post');
    }

    public function create(User $user): bool
    {
        return $user->can('create_post');
    }

    public function update(User $user): bool
    {
        return $user->can('update_post');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_post');
    }
}
