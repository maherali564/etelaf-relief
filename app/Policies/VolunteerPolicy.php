<?php

namespace App\Policies;

use App\Models\User;

class VolunteerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_volunteer');
    }

    public function view(User $user): bool
    {
        return $user->can('view_volunteer');
    }

    public function create(User $user): bool
    {
        return $user->can('create_volunteer');
    }

    public function update(User $user): bool
    {
        return $user->can('update_volunteer');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_volunteer');
    }

    public function review(User $user): bool
    {
        return $user->can('review_volunteer');
    }
}
