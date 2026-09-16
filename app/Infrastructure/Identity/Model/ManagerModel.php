<?php

namespace App\Infrastructure\Identity\Model;

use Database\Factories\ManagerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
final class ManagerModel extends Authenticatable
{
    /** @use HasFactory<ManagerFactory> */
    use HasApiTokens, HasFactory;

    protected $table = 'managers';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): ManagerFactory
    {
        return ManagerFactory::new();
    }
}
