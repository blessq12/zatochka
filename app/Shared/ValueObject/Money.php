<?php

namespace App\Shared\ValueObject;

final readonly class Money
{
    public function __construct(
        public string $amount,
        public string $currency = 'RUB',
    ) {}

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
