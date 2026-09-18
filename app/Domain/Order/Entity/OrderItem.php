<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\OrderItemKind;
use App\Shared\Domain\DomainException;

final class OrderItem
{
    public function __construct(
        private ?int $id,
        private OrderItemKind $kind,
        private ?string $title,
        private ?int $quantity,
        private ?int $equipmentId,
        private ?string $problem,
        private int $position = 0,
    ) {
        $this->assertInvariants();
    }

    public static function sharpening(
        string $title,
        int $quantity,
        int $position = 0,
    ): self {
        return new self(null, OrderItemKind::Sharpening, $title, $quantity, null, null, $position);
    }

    public static function repair(
        int $equipmentId,
        ?string $problem = null,
        int $position = 0,
    ): self {
        return new self(null, OrderItemKind::Repair, null, null, $equipmentId, $problem, $position);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function kind(): OrderItemKind
    {
        return $this->kind;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function quantity(): ?int
    {
        return $this->quantity;
    }

    public function equipmentId(): ?int
    {
        return $this->equipmentId;
    }

    public function problem(): ?string
    {
        return $this->problem;
    }

    public function position(): int
    {
        return $this->position;
    }

    private function assertInvariants(): void
    {
        if ($this->kind === OrderItemKind::Sharpening) {
            if ($this->title === null || trim($this->title) === '') {
                throw new DomainException('Sharpening item requires title.');
            }
            if ($this->quantity === null || $this->quantity < 1) {
                throw new DomainException('Sharpening item requires quantity >= 1.');
            }
            if ($this->equipmentId !== null) {
                throw new DomainException('Sharpening item cannot have equipment_id.');
            }

            return;
        }

        if ($this->kind === OrderItemKind::Repair) {
            if ($this->equipmentId === null || $this->equipmentId < 1) {
                throw new DomainException('Repair item requires equipment_id.');
            }
            if ($this->title !== null || $this->quantity !== null) {
                throw new DomainException('Repair item cannot have title or quantity.');
            }

            return;
        }

        throw new DomainException('Unknown order item kind.');
    }
}
