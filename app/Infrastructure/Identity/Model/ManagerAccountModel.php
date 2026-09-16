<?php

namespace App\Infrastructure\Identity\Model;

use Database\Factories\ManagerAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 */
final class ManagerAccountModel extends Authenticatable
{
    /** @use HasFactory<ManagerAccountFactory> */
    use HasApiTokens, HasFactory;

    protected $table = 'manager_accounts';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory(): ManagerAccountFactory
    {
        return ManagerAccountFactory::new();
    }
}
