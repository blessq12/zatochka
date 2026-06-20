<?php

namespace App\Infrastructure\Company\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class CompanySettingModel extends Model
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
