<?php

namespace App\Domain\Crm\Aggregate;

use App\Domain\Crm\ActorType;

abstract class Actor
{
    public function __construct(
        private ?int $id,
        private int $profileAdditionalId,
        private bool $deleted = false,
    ) {}

    abstract public function type(): ActorType;

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function profileAdditionalId(): int
    {
        return $this->profileAdditionalId;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function markDeleted(): void
    {
        $this->deleted = true;
    }
}
