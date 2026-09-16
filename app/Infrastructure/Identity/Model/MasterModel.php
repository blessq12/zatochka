<?php

namespace App\Infrastructure\Identity\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
final class MasterModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'masters';

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
}
