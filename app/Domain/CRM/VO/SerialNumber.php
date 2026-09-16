<?php

namespace App\Domain\CRM\VO;

use App\Shared\Domain\DomainException;

final readonly class SerialNumber
{
    public function __construct(
        public string $value,
    ) {
        if (trim($this->value) === '') {
            throw new DomainException('Serial number cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
