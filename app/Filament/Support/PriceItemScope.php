<?php

namespace App\Filament\Support;

use App\Domain\Pricing\Enum\PriceType;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceBlockModel;
use App\Infrastructure\Pricing\Persistence\Eloquent\PriceItemModel;
use Illuminate\Database\Eloquent\Builder;

final class PriceItemScope
{
    /** @param Builder<PriceItemModel> $query */
    public static function byType(Builder $query, PriceType $type): Builder
    {
        return $query->whereHas(
            'block',
            fn (Builder $blockQuery) => $blockQuery->where('type', $type),
        );
    }

    public static function blockIdForType(PriceType $type): int
    {
        $block = PriceBlockModel::query()
            ->where('type', $type)
            ->orderBy('sort_order')
            ->firstOrFail();

        return $block->id;
    }

    public static function nextSortOrder(int $blockId): int
    {
        $maxOrder = PriceItemModel::query()
            ->where('price_block_id', $blockId)
            ->max('sort_order');

        return ((int) $maxOrder) + 1;
    }
}
