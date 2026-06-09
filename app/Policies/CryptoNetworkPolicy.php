<?php

namespace App\Policies;

use App\Models\User;

class CryptoNetworkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_crypto_network');
    }

    public function view(User $user): bool
    {
        return $user->can('view_crypto_network');
    }

    public function create(User $user): bool
    {
        return $user->can('create_crypto_network');
    }

    public function update(User $user): bool
    {
        return $user->can('update_crypto_network');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_crypto_network');
    }
}
