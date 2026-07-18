<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\FaqCatalog;
use App\Domain\SiteContent\Repository\FaqCatalogRepository;
use App\Infrastructure\SiteContent\Mapper\FaqCatalogMapper;
use App\Infrastructure\SiteContent\Model\FaqItemModel;
use Illuminate\Support\Facades\DB;

final readonly class EloquentFaqCatalogRepository implements FaqCatalogRepository
{
    public function __construct(
        private FaqCatalogMapper $mapper,
    ) {}

    public function get(): FaqCatalog
    {
        return $this->mapper->toDomain(FaqItemModel::query()->orderBy('sort_order')->get());
    }

    public function save(FaqCatalog $catalog): void
    {
        DB::transaction(function () use ($catalog): void {
            $rows = $this->mapper->toPersistence($catalog);
            $ids = array_column($rows, 'id');

            if ($ids === []) {
                FaqItemModel::query()->delete();
            } else {
                FaqItemModel::query()->whereNotIn('id', $ids)->delete();
            }

            foreach ($rows as $row) {
                FaqItemModel::query()->updateOrCreate(
                    ['id' => $row['id']],
                    $row,
                );
            }
        });
    }
}
