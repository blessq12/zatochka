<?php

namespace App\Domain\Feedback\VO;

use App\Shared\Domain\DomainException;

final readonly class Rating
{
    public function __construct(
        public int $value,
    ) {
        if ($this->value < 1 || $this->value > 5) {
            throw new DomainException('Rating must be between 1 and 5.');
        }
    }
}
