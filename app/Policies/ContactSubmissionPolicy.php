<?php

namespace App\Policies;

use App\Models\User;

class ContactSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_contact_submission');
    }

    public function view(User $user): bool
    {
        return $user->can('view_contact_submission');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_contact_submission');
    }
}
