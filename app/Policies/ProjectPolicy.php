<?php

namespace App\Policies;

use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_project');
    }

    public function view(User $user): bool
    {
        return $user->can('view_project');
    }

    public function create(User $user): bool
    {
        return $user->can('create_project');
    }

    public function update(User $user): bool
    {
        return $user->can('update_project');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_project');
    }
}
