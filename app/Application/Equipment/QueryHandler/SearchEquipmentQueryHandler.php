<?php

namespace App\Application\Equipment\QueryHandler;

use App\Application\Equipment\Query\SearchEquipmentQuery;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;

final class SearchEquipmentQueryHandler
{
    public function __construct(
        private EquipmentRepositoryInterface $equipment,
    ) {}

    /**
     * @return array{items: list<\App\Domain\Equipment\Entity\Equipment>, total: int, page: int, per_page: int}
     */
    public function handle(SearchEquipmentQuery $query): array
    {
        $result = $this->equipment->search($query->query, $query->page, $query->perPage);

        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->page,
            'per_page' => $query->perPage,
        ];
    }
}
