<?php

namespace App\Domain\Workshop\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class PerformedWork
{
    public function __construct(
        public EntityId $id,
        public EntityId $orderItemId,
        public EntityId $masterId,
        public string $description,
        public ?EntityId $equipmentComponentId = null,
        public DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {
        if (trim($this->description) === '') {
            throw new DomainException('Performed work description cannot be empty.');
        }
    }

    public function withDescription(string $description): self
    {
        return new self(
            $this->id,
            $this->orderItemId,
            $this->masterId,
            $description,
            $this->equipmentComponentId,
            $this->createdAt,
        );
    }
}
