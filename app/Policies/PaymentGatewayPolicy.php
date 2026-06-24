<?php

namespace App\Policies;

use App\Models\User;

class PaymentGatewayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_payment_gateway');
    }

    public function view(User $user): bool
    {
        return $user->can('view_payment_gateway');
    }

    public function create(User $user): bool
    {
        return $user->can('create_payment_gateway');
    }

    public function update(User $user): bool
    {
        return $user->can('update_payment_gateway');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_payment_gateway');
    }
}
