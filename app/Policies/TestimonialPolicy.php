<?php

namespace App\Policies;

use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_testimonial');
    }

    public function view(User $user): bool
    {
        return $user->can('view_testimonial');
    }

    public function create(User $user): bool
    {
        return $user->can('create_testimonial');
    }

    public function update(User $user): bool
    {
        return $user->can('update_testimonial');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_testimonial');
    }
}
