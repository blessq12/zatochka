<?php

namespace App\Infrastructure\Identity\Model;

use Database\Factories\MasterAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 */
final class MasterAccountModel extends Authenticatable
{
    /** @use HasFactory<MasterAccountFactory> */
    use HasApiTokens, HasFactory;

    protected $table = 'master_accounts';

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

    protected static function newFactory(): MasterAccountFactory
    {
        return MasterAccountFactory::new();
    }
}
