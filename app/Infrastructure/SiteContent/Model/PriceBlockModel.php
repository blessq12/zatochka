<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PriceBlockModel extends Model
{
    protected $table = 'site_price_blocks';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'type',
        'title',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<PriceItemModel, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PriceItemModel::class, 'price_block_id')
            ->orderBy('sort_order');
    }
}
