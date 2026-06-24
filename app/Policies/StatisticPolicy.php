<?php

namespace App\Policies;

use App\Models\User;

class StatisticPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_statistic');
    }

    public function view(User $user): bool
    {
        return $user->can('view_statistic');
    }

    public function create(User $user): bool
    {
        return $user->can('create_statistic');
    }

    public function update(User $user): bool
    {
        return $user->can('update_statistic');
    }

    public function delete(User $user): bool
    {
        return $user->can('delete_statistic');
    }
}
