<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;

final class DeliveryInfoModel extends Model
{
    protected $table = 'site_delivery_infos';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'free_conditions',
        'advantages',
    ];

    protected function casts(): array
    {
        return [
            'free_conditions' => 'array',
            'advantages' => 'array',
        ];
    }
}
