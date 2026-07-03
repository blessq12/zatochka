<?php

namespace App\Application\Pricing\CommandHandler;

use App\Application\Pricing\Command\SavePriceItemCommand;
use App\Domain\Pricing\Entity\PriceItem;
use App\Domain\Pricing\Repository\PriceItemRepositoryInterface;

final class SavePriceItemHandler
{
    public function __construct(
        private PriceItemRepositoryInterface $priceItems,
    ) {}

    public function handle(SavePriceItemCommand $command): PriceItem
    {
        return $this->priceItems->save(new PriceItem(
            id: $command->id,
            priceBlockId: $command->priceBlockId,
            name: $command->name,
            price: number_format((float) $command->price, 2, '.', ''),
            pricePrefix: $command->pricePrefix,
            description: $command->description,
            sortOrder: $command->sortOrder,
        ));
    }
}
