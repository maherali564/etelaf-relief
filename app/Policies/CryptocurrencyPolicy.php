<?php

namespace App\Policies;

use App\Models\User;

class CryptocurrencyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cryptocurrency');
    }

    public function view(User $user): bool
    {
        return $user->can('view_cryptocurrency');
    }

    public function create(User $user): bool
    {
        return $user->can('create_cryptocurrency');
    }

    public function update(User $user): bool
    {
        return $user->can('update_cryptocurrency');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_cryptocurrency');
    }
}
