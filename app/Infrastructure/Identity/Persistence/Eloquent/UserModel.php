<?php

namespace App\Infrastructure\Identity\Persistence\Eloquent;

use App\Domain\Identity\Enum\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'surname',
        'email',
        'role',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isMaster(): bool
    {
        return $this->role === UserRole::Master;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isManager();
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
