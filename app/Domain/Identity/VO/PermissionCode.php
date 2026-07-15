<?php

namespace App\Domain\Identity\VO;

use App\Shared\Domain\DomainException;

final readonly class PermissionCode
{
    public function __construct(
        public string $value,
    ) {
        if (trim($this->value) === '') {
            throw new DomainException('Permission code cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
