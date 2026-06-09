<?php

namespace App\Policies;

use App\Models\User;

class VolunteerOpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_volunteer_opportunity');
    }

    public function view(User $user): bool
    {
        return $user->can('view_volunteer_opportunity');
    }

    public function create(User $user): bool
    {
        return $user->can('create_volunteer_opportunity');
    }

    public function update(User $user): bool
    {
        return $user->can('update_volunteer_opportunity');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_volunteer_opportunity');
    }
}
