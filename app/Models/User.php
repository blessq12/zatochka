<?php

namespace App\Models;

use App\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

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
        'branch_id',
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
        return ! empty(array_intersect($roles, $this->role ?? []));
    }

    public function addRole(string $role): void
    {
        $allowedRoles = array_column(UserRole::cases(), 'value');
        if (! in_array($role, $allowedRoles)) {
            throw new \InvalidArgumentException("Недопустимая роль: {$role}");
        }

        $roles = $this->role ?? [];
        if (! in_array($role, $roles)) {
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
        $allowedRoles = array_column(UserRole::cases(), 'value');
        $invalidRoles = array_diff($roles, $allowedRoles);

        if (! empty($invalidRoles)) {
            throw new \InvalidArgumentException('Недопустимые роли: ' . implode(', ', $invalidRoles));
        }

        $this->role = array_unique($roles);
    }

    public function getRoles(): array
    {
        return $this->role ?? [];
    }

    public function getRoleLabels(): array
    {
        return array_map(fn($role) => UserRole::getAll()[$role] ?? $role, $this->getRoles());
    }

    /**
     * Получить все доступные роли
     */
    public static function getAvailableRoles(): array
    {
        return UserRole::getAll();
    }

    /**
     * Получить роли для выбора в формах (без админа)
     */
    public static function getSelectableRoles(): array
    {
        return UserRole::getSelectable();
    }

    // Связи
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
