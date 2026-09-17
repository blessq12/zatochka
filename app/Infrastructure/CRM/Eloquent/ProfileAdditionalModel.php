<?php

namespace App\Infrastructure\Crm\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProfileAdditionalModel extends Model
{
    use SoftDeletes;

    protected $table = 'profile_additionals';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }
}
