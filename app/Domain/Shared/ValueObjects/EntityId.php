<?php

namespace App\Domain\Shared\ValueObjects;

final readonly class EntityId
{
    public function __construct(
        public int $value,
    ) {}

    public static function fromNullable(?int $id): ?self
    {
        return $id === null ? null : new self($id);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
