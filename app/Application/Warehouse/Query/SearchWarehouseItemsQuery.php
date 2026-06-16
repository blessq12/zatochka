<?php

namespace App\Application\Warehouse\Query;

final readonly class SearchWarehouseItemsQuery
{
    public function __construct(
        public ?string $query,
        public int $page = 1,
        public int $perPage = 20,
    ) {}
}
