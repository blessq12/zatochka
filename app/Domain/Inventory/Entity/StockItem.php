<?php

namespace App\Domain\Inventory\Entity;

use App\Domain\Inventory\Event\MaterialReceived;
use App\Domain\Inventory\Event\MaterialWrittenOff;
use App\Domain\Inventory\Event\StockChanged;
use App\Domain\Inventory\VO\MovementType;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class StockItem extends AggregateRoot
{
    private Quantity $quantityOnHand;

    /** @var list<WarehouseMovement> */
    private array $movements = [];

    private function __construct(
        private readonly EntityId $id,
        private readonly Material $material,
        Quantity $initialQuantity,
    ) {
        $this->quantityOnHand = $initialQuantity;
    }

    public static function open(EntityId $id, Material $material, ?Quantity $initialQuantity = null): self
    {
        return new self($id, $material, $initialQuantity ?? new Quantity('0'));
    }

    /**
     * @param list<WarehouseMovement> $movements
     */
    public static function reconstitute(
        EntityId $id,
        Material $material,
        Quantity $quantityOnHand,
        array $movements = [],
    ): self {
        $item = new self($id, $material, $quantityOnHand);
        $item->movements = $movements;

        return $item;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function material(): Material
    {
        return $this->material;
    }

    public function quantityOnHand(): Quantity
    {
        return $this->quantityOnHand;
    }

    /** @return list<WarehouseMovement> */
    public function movements(): array
    {
        return $this->movements;
    }

    public function receive(
        EntityId $movementId,
        Quantity $quantity,
        ?string $comment = null,
        ?string $orderId = null,
        ?int $orderItemId = null,
    ): void {
        if ((float) $quantity->value <= 0) {
            throw new DomainException('Received quantity must be positive.');
        }

        $this->quantityOnHand = $this->quantityOnHand->add($quantity);
        $this->movements[] = new WarehouseMovement(
            $movementId,
            MovementType::Receipt,
            $quantity,
            comment: $comment,
            orderId: $orderId,
            orderItemId: $orderItemId,
        );
        $this->record(new MaterialReceived($this->id, $this->material->id(), $quantity->value));
        $this->record(new StockChanged($this->id, $this->material->id(), $this->quantityOnHand->value));
    }

    public function writeOff(
        EntityId $movementId,
        Quantity $quantity,
        ?string $comment = null,
        ?string $orderId = null,
        ?int $orderItemId = null,
    ): void {
        if ((float) $quantity->value <= 0) {
            throw new DomainException('Write-off quantity must be positive.');
        }

        $this->quantityOnHand = $this->quantityOnHand->subtract($quantity);
        $this->movements[] = new WarehouseMovement(
            $movementId,
            MovementType::WriteOff,
            $quantity,
            comment: $comment,
            orderId: $orderId,
            orderItemId: $orderItemId,
        );
        $this->record(new MaterialWrittenOff($this->id, $this->material->id(), $quantity->value));
        $this->record(new StockChanged($this->id, $this->material->id(), $this->quantityOnHand->value));
    }

    public function adjust(
        EntityId $movementId,
        Quantity $newQuantity,
        ?string $comment = null,
        ?string $orderId = null,
        ?int $orderItemId = null,
    ): void {
        $this->quantityOnHand = $newQuantity;
        $this->movements[] = new WarehouseMovement(
            $movementId,
            MovementType::Adjustment,
            $newQuantity,
            comment: $comment,
            orderId: $orderId,
            orderItemId: $orderItemId,
        );
        $this->record(new StockChanged($this->id, $this->material->id(), $this->quantityOnHand->value));
    }
}
