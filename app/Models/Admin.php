<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'full_name',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'      => 'hashed',
            'is_active'     => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if admin possesses a specific role.
     */
    public function hasRole(string|array $roles): bool
    {
        if (! $this->relationLoaded('role')) {
            $this->load('role');
        }

        if (! $this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->name, $roles, true);
        }

        return $this->role->name === $roles;
    }

    /**
     * Check if admin possesses a specific permission through their role.
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->relationLoaded('role.permissions')) {
            $this->load('role.permissions');
        }

        if (! $this->role) {
            return false;
        }

        // Super Admin role automatically possesses all permissions
        if ($this->role->name === 'Super Admin') {
            return true;
        }

        return $this->role->permissions->contains('name', $permission);
    }

    /**
     * Check if admin possesses any of the provided permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
