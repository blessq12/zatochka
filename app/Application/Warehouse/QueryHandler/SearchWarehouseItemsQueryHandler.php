<?php

namespace App\Application\Warehouse\QueryHandler;

use App\Application\Warehouse\Query\SearchWarehouseItemsQuery;
use App\Domain\Warehouse\Repository\WarehouseItemRepositoryInterface;

final class SearchWarehouseItemsQueryHandler
{
    public function __construct(
        private WarehouseItemRepositoryInterface $items,
    ) {}

    /**
     * @return array{items: list<\App\Domain\Warehouse\Entity\WarehouseItem>, total: int, page: int, per_page: int}
     */
    public function handle(SearchWarehouseItemsQuery $query): array
    {
        $result = $this->items->search($query->query, $query->page, $query->perPage);

        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->page,
            'per_page' => $query->perPage,
        ];
    }
}
