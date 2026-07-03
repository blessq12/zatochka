<?php

namespace App\Infrastructure\Pricing\Persistence\Eloquent;

use App\Domain\Pricing\Enum\PricePrefix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceItemModel extends Model
{
    protected $table = 'price_items';

    protected $fillable = [
        'price_block_id',
        'name',
        'price',
        'price_prefix',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_prefix' => PricePrefix::class,
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(PriceBlockModel::class, 'price_block_id');
    }
}
