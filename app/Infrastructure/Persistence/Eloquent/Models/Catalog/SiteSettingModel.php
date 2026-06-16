<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class SiteSettingModel extends Model
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
