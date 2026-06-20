<?php

namespace App\Application\Warehouse\Query;

use App\Domain\Warehouse\Enum\WarehouseItemType;

final readonly class SearchWarehouseItemsQuery
{
    public function __construct(
        public ?string $query,
        public ?WarehouseItemType $type = null,
        public int $page = 1,
        public int $perPage = 20,
    ) {}
}
