<?php

namespace App\Infrastructure\Identity\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property int $client_id
 * @property string $phone
 * @property string $password
 */
final class ClientAccountModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'client_accounts';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'client_id',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
