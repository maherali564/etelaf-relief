<?php

namespace App\Policies;

use App\Models\User;

class CryptoTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_crypto_transaction');
    }

    public function view(User $user): bool
    {
        return $user->can('view_crypto_transaction');
    }

    public function create(User $user): bool
    {
        return $user->can('create_crypto_transaction');
    }

    public function update(User $user): bool
    {
        return $user->can('update_crypto_transaction');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_crypto_transaction');
    }
}
