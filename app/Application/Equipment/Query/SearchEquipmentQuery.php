<?php

namespace App\Application\Equipment\Query;

final readonly class SearchEquipmentQuery
{
    public function __construct(
        public ?string $query,
        public int $page = 1,
        public int $perPage = 20,
    ) {}
}
