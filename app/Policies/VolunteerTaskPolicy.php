<?php

namespace App\Policies;

use App\Models\User;

class VolunteerTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_volunteer_task');
    }

    public function view(User $user): bool
    {
        return $user->can('view_volunteer_task');
    }

    public function create(User $user): bool
    {
        return $user->can('create_volunteer_task');
    }

    public function update(User $user): bool
    {
        return $user->can('update_volunteer_task');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_volunteer_task');
    }
}
