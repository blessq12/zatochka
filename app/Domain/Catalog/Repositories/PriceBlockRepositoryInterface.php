<?php

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Entities\PriceBlock;

interface PriceBlockRepositoryInterface
{
    public function findById(int $id): ?PriceBlock;

    public function save(PriceBlock $block): PriceBlock;
}
