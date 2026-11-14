<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'username',
        'password',
        'role_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->role_id !== null && $this->is_active;
    }

    /**
     * Backward-compatible alias for full_name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name ?? $this->username ?? '';
    }

    public function setFullNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->name)) {
                $user->name = $user->username ?: explode('@', (string) $user->email)[0];
            }
        });
    }

    /**
     * Check if user possesses a specific role.
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
     * Check if user possesses a specific permission through their role.
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
     * Check if user possesses any of the provided permissions.
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
