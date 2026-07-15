<?php

namespace App\Domain\Pricing\Service;

use App\Domain\Pricing\Entity\Estimate;
use App\Domain\Pricing\Entity\ItemPrice;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class PriceCalculationService
{
    public function bindBasePrice(Estimate $estimate, EntityId $itemPriceId, Money $baseAmount): void
    {
        $estimate->attachItemPrice(new ItemPrice(
            $itemPriceId,
            $estimate->orderItemId(),
            $baseAmount,
        ));
    }
}
