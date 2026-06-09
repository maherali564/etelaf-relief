<?php

namespace App\Policies;

use App\Models\User;

class NewsletterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_newsletter');
    }

    public function view(User $user): bool
    {
        return $user->can('view_newsletter');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_newsletter');
    }
}
