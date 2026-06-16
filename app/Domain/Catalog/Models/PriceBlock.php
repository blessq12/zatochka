<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\PriceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceBlock extends Model
{
    protected $table = 'price_blocks';

    protected $fillable = [
        'type',
        'title',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PriceType::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceItem::class, 'price_block_id')->orderBy('sort_order');
    }
}
