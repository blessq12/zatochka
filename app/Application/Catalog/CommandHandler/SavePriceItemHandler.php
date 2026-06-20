<?php

namespace App\Application\Catalog\CommandHandler;

use App\Application\Catalog\Command\SavePriceItemCommand;
use App\Domain\Catalog\Entity\PriceItem;
use App\Domain\Catalog\Repository\PriceItemRepositoryInterface;

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
            description: $command->description,
            sortOrder: $command->sortOrder,
        ));
    }
}
