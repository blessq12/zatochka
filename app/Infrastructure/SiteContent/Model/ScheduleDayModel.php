<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;

final class ScheduleDayModel extends Model
{
    protected $table = 'site_schedule_days';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'is_day_off',
        'day_off_text',
        'workshop',
        'delivery',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_day_off' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
