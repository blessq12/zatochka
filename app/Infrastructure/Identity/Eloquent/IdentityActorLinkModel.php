<?php

namespace App\Infrastructure\Identity\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IdentityActorLinkModel extends Model
{
    protected $table = 'identity_actor_links';

    protected $fillable = [
        'identity_id',
        'actor_type',
        'actor_id',
    ];

    public function identity(): BelongsTo
    {
        return $this->belongsTo(IdentityModel::class, 'identity_id');
    }
}
