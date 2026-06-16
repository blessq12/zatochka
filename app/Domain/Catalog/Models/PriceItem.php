<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceItem extends Model
{
    protected $table = 'price_items';

    protected $fillable = [
        'price_block_id',
        'name',
        'price',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(PriceBlock::class, 'price_block_id');
    }
}
