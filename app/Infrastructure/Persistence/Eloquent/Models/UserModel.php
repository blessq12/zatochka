<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    /**
     * The guard name that should be used for authentication.
     */
    protected $guard_name = 'manager';

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'is_deleted',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    /**
     * Get the guard name for the model.
     */
    public function getGuardName(): string
    {
        return 'manager';
    }
}
