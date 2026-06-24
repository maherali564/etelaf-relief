<?php

namespace App\Policies;

use App\Models\User;

class MediaItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_media_item');
    }

    public function view(User $user): bool
    {
        return $user->can('view_media_item');
    }

    public function create(User $user): bool
    {
        return $user->can('create_media_item');
    }

    public function update(User $user): bool
    {
        return $user->can('update_media_item');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_media_item');
    }
}
