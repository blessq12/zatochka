<?php

namespace App\Domain\Pricing\Repository;

use App\Domain\Pricing\Entity\PriceBlock;

interface PriceBlockRepositoryInterface
{
    public function findById(int $id): ?PriceBlock;

    public function save(PriceBlock $block): PriceBlock;

    /** @return list<PriceBlock> */
    public function findAllOrdered(): array;
}
