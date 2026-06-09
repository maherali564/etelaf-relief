<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_donation');
    }

    public function view(User $user, Donation $donation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can('view_donation');
    }

    public function create(User $user): bool
    {
        return $user->can('create_donation');
    }

    public function update(User $user, Donation $donation): bool
    {
        return $user->can('update_donation');
    }

    public function delete(User $user, Donation $donation): bool
    {
        return $user->can('delete_donation');
    }

    public function review(User $user, Donation $donation): bool
    {
        return $user->can('review_donation');
    }
}
