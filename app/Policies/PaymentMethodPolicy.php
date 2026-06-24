<?php

namespace App\Policies;

use App\Models\User;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_payment_method');
    }

    public function view(User $user): bool
    {
        return $user->can('view_payment_method');
    }

    public function create(User $user): bool
    {
        return $user->can('create_payment_method');
    }

    public function update(User $user): bool
    {
        return $user->can('update_payment_method');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_payment_method');
    }
}
