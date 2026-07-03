<?php

namespace App\Infrastructure\Pricing\Persistence\Eloquent;

use App\Domain\Pricing\Enum\PriceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceBlockModel extends Model
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
        return $this->hasMany(PriceItemModel::class, 'price_block_id')->orderBy('sort_order');
    }
}
