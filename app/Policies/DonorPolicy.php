<?php

namespace App\Policies;

use App\Models\User;

class DonorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_donor');
    }

    public function view(User $user): bool
    {
        return $user->can('view_donor');
    }

    public function create(User $user): bool
    {
        return $user->can('create_donor');
    }

    public function update(User $user): bool
    {
        return $user->can('update_donor');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_donor');
    }
}
