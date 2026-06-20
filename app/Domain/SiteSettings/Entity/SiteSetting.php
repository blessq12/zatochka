<?php

namespace App\Domain\SiteSettings\Entity;

final class SiteSetting
{
    public function __construct(
        private ?int $id,
        private string $key,
        private array $value,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): array
    {
        return $this->value;
    }
}
