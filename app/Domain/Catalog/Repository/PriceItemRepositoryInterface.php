<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\PriceItem;

interface PriceItemRepositoryInterface
{
    public function save(PriceItem $item): PriceItem;
}
