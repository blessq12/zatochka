<?php

namespace App\Infrastructure\Crm\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class ActorModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function profileAdditional(): BelongsTo
    {
        return $this->belongsTo(ProfileAdditionalModel::class, 'profile_additional_id');
    }
}
