<?php

namespace App\Models;

use App\Domain\Company\Enum\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
// ... existing code ...

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_deleted' => 'boolean',
        'role' => 'array',
    ];

    // Связи

    // public function inventoryTransactions()
    // {
    //     return $this->hasMany(InventoryTransaction::class);
    // }

    // Scope для активных пользователей
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    // Методы для работы с ролями
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->role ?? []);
    }

    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($roles, $this->role ?? []));
    }

    public function addRole(string $role): void
    {
        if (!UserRole::tryFrom($role)) {
            throw new \InvalidArgumentException("Недопустимая роль: {$role}");
        }

        $roles = $this->role ?? [];
        if (!in_array($role, $roles)) {
            $roles[] = $role;
            $this->role = $roles;
        }
    }

    public function removeRole(string $role): void
    {
        $roles = $this->role ?? [];
        $this->role = array_values(array_filter($roles, fn($r) => $r !== $role));
    }

    public function setRoles(array $roles): void
    {
        if (!UserRole::validate($roles)) {
            throw new \InvalidArgumentException('Недопустимые роли');
        }

        $this->role = array_unique($roles);
    }

    public function getRoles(): array
    {
        return $this->role ?? [];
    }

    public function getRoleEnums(): array
    {
        return UserRole::fromArray($this->getRoles());
    }

    public function hasRoleEnum(UserRole $role): bool
    {
        return $this->hasRole($role->value);
    }
}
