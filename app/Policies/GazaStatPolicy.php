<?php

namespace App\Policies;

use App\Models\User;

class GazaStatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_gaza_stat');
    }

    public function view(User $user): bool
    {
        return $user->can('view_gaza_stat');
    }

    public function create(User $user): bool
    {
        return $user->can('create_gaza_stat');
    }

    public function update(User $user): bool
    {
        return $user->can('update_gaza_stat');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_gaza_stat');
    }
}
