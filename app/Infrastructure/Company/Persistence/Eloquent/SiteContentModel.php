<?php

namespace App\Infrastructure\Company\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class SiteContentModel extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
