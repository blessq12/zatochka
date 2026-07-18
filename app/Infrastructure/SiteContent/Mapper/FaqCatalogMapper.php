<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\FaqCatalog;
use App\Domain\SiteContent\Entity\FaqItem;
use App\Infrastructure\SiteContent\Model\FaqItemModel;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Collection;

final class FaqCatalogMapper
{
    /** @param Collection<int, FaqItemModel> $models */
    public function toDomain(Collection $models): FaqCatalog
    {
        $items = $models
            ->sortBy('sort_order')
            ->values()
            ->map(static fn (FaqItemModel $model): FaqItem => FaqItem::reconstitute(
                new EntityId((int) $model->id),
                (string) $model->question,
                array_values((array) $model->answer_lines),
                (int) $model->sort_order,
            ))
            ->all();

        return FaqCatalog::reconstitute($items);
    }

    /** @return list<array<string, mixed>> */
    public function toPersistence(FaqCatalog $catalog): array
    {
        $rows = [];

        foreach ($catalog->items() as $item) {
            $rows[] = [
                'id' => $item->id()->value,
                'question' => $item->question(),
                'answer_lines' => $item->answerLines(),
                'sort_order' => $item->sortOrder(),
            ];
        }

        return $rows;
    }
}
