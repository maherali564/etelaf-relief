<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'is_active',
        'avatar',
        'preferred_locale',
        'phone',
        'is_online',
        'last_seen_at',
        'can_chat',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'can_chat' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && ($this->is_admin || $this->roles()->exists());
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_admin || $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('admin');
    }

    public function isEditor(): bool
    {
        return $this->isAdmin() || $this->hasRole('editor');
    }

    public function isSupporter(): bool
    {
        return $this->isAdmin() || $this->hasRole('supporter');
    }
}
