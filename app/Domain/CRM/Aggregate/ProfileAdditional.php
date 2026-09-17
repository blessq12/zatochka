<?php

namespace App\Domain\Crm\Aggregate;

final class ProfileAdditional
{
    public function __construct(
        private ?int $id,
        private bool $deleted = false,
    ) {}

    public static function create(): self
    {
        return new self(null);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
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
