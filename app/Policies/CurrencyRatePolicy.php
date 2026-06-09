<?php

namespace App\Policies;

use App\Models\User;

class CurrencyRatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_currency_rate');
    }

    public function view(User $user): bool
    {
        return $user->can('view_currency_rate');
    }

    public function create(User $user): bool
    {
        return $user->can('create_currency_rate');
    }

    public function update(User $user): bool
    {
        return $user->can('update_currency_rate');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_currency_rate');
    }
}
