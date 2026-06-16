<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\PriceBlock;

interface PriceBlockRepositoryInterface
{
    public function findById(int $id): ?PriceBlock;

    public function save(PriceBlock $block): PriceBlock;

    /** @return list<PriceBlock> */
    public function findAllOrdered(): array;
}
