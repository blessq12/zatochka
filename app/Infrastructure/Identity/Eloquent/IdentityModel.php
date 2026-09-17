<?php

namespace App\Infrastructure\Identity\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class IdentityModel extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'identities';

    protected $fillable = [
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function actorLink(): HasOne
    {
        return $this->hasOne(IdentityActorLinkModel::class, 'identity_id');
    }
}
