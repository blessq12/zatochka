<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PriceItemModel extends Model
{
    protected $table = 'site_price_items';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'price_block_id',
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

    /** @return BelongsTo<PriceBlockModel, $this> */
    public function block(): BelongsTo
    {
        return $this->belongsTo(PriceBlockModel::class, 'price_block_id');
    }
}
