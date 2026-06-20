<?php

namespace App\Domain\Pricing\Repository;

use App\Domain\Pricing\Entity\PriceItem;

interface PriceItemRepositoryInterface
{
    public function save(PriceItem $item): PriceItem;

    /** @return list<PriceItem> */
    public function findByPriceBlockId(int $priceBlockId): array;
}
