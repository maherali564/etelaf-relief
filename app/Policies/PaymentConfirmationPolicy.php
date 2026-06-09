<?php

namespace App\Policies;

use App\Models\User;

class PaymentConfirmationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_payment_confirmation');
    }

    public function view(User $user): bool
    {
        return $user->can('view_payment_confirmation');
    }

    public function create(User $user): bool
    {
        return $user->can('create_payment_confirmation');
    }

    public function update(User $user): bool
    {
        return $user->can('update_payment_confirmation');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_payment_confirmation');
    }
}
