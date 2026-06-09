<?php

namespace App\Policies;

use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_faq');
    }

    public function view(User $user): bool
    {
        return $user->can('view_faq');
    }

    public function create(User $user): bool
    {
        return $user->can('create_faq');
    }

    public function update(User $user): bool
    {
        return $user->can('update_faq');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_faq');
    }
}
