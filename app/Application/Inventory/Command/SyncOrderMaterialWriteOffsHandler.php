<?php

namespace App\Application\Inventory\Command;

use App\Application\Inventory\ReadPort\OrderMaterialWriteOffReadPort;
use App\Application\Shared\UnitOfWork;
use App\Shared\Domain\DomainException;

final readonly class SyncOrderMaterialWriteOffsHandler
{
    public function __construct(
        private OrderMaterialWriteOffReadPort $writeOffs,
        private WriteOffMaterialHandler $writeOff,
        private ReverseOrderMaterialWriteOffHandler $reverse,
        private ReplaceOrderMaterialWriteOffHandler $replace,
        private UnitOfWork $unitOfWork,
    ) {}

    public function handle(SyncOrderMaterialWriteOffsCommand $command): void
    {
        $this->unitOfWork->execute(function () use ($command): void {
            $current = $this->writeOffs->listActiveByOrderId($command->orderId);
            $currentById = [];
            foreach ($current as $line) {
                $currentById[$line->movementId] = $line;
            }

            $desiredIds = [];
            foreach ($command->lines as $item) {
                if ($item->movementId !== null) {
                    $desiredIds[$item->movementId] = true;
                }
            }

            foreach ($current as $line) {
                if (! isset($desiredIds[$line->movementId])) {
                    $this->reverse->handle(new ReverseOrderMaterialWriteOffCommand(
                        $line->stockItemId,
                        $line->movementId,
                    ));
                }
            }

            foreach ($command->lines as $item) {
                $quantity = trim($item->quantity);
                $unitPrice = trim($item->unitPrice);

                if ($quantity === '' || (float) $quantity <= 0) {
                    throw new DomainException('Write-off quantity must be positive.');
                }

                if ($unitPrice === '' || (float) $unitPrice <= 0) {
                    throw new DomainException('Unit price is required when writing off material to an order.');
                }

                if ($item->movementId === null) {
                    $this->writeOff->handle(new WriteOffMaterialCommand(
                        $item->stockItemId,
                        $quantity,
                        $item->comment,
                        $command->orderId,
                        $item->orderItemId,
                        unitPrice: $unitPrice,
                        currency: $item->currency,
                    ));

                    continue;
                }

                $existing = $currentById[$item->movementId] ?? null;
                if ($existing === null) {
                    throw new DomainException(sprintf('Active write-off %d not found.', $item->movementId));
                }

                $sameStock = $existing->stockItemId === $item->stockItemId;
                $sameQty = number_format((float) $existing->quantity, 3, '.', '') === number_format((float) $quantity, 3, '.', '');
                $samePrice = number_format((float) $existing->unitPrice, 2, '.', '') === number_format((float) $unitPrice, 2, '.', '');
                $sameOrderItem = $existing->orderItemId === $item->orderItemId;
                $sameComment = ($existing->comment ?? null) === ($item->comment ?? null);

                if ($sameStock && $sameQty && $samePrice && $sameOrderItem && $sameComment) {
                    continue;
                }

                if (! $sameStock) {
                    throw new DomainException('Cannot change stock item of an existing write-off; remove and add a new line.');
                }

                $this->replace->handle(new ReplaceOrderMaterialWriteOffCommand(
                    $item->stockItemId,
                    $item->movementId,
                    $quantity,
                    $unitPrice,
                    $item->currency,
                    $item->orderItemId,
                    $item->comment,
                ));
            }
        });
    }
}
