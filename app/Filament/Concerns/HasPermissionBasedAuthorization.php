<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Str;

trait HasPermissionBasedAuthorization
{
    public static function getPermissionSlug(): string
    {
        $class = class_basename(static::class);

        return Str::snake(Str::before($class, 'Resource'));
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'view_any_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'view_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'create_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'update_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'delete_'.static::getPermissionSlug();

        return $user->can($permission);
    }
}
