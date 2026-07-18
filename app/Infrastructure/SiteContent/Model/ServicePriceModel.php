<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;

final class ServicePriceModel extends Model
{
    protected $table = 'site_service_prices';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'category',
        'name',
        'price',
        'prefix',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
