<?php

namespace App\Policies;

use App\Models\User;

class DonationSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_donation_submission');
    }

    public function view(User $user): bool
    {
        return $user->can('view_donation_submission');
    }

    public function create(User $user): bool
    {
        return $user->can('create_donation_submission');
    }

    public function update(User $user): bool
    {
        return $user->can('update_donation_submission');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_donation_submission');
    }
}
