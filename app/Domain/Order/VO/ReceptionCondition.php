<?php

namespace App\Domain\Order\VO;

final readonly class ReceptionCondition
{
    public function __construct(
        public string $description,
        public ?string $visualNotes = null,
    ) {
        if (trim($this->description) === '') {
            throw new \InvalidArgumentException('Reception condition description cannot be empty.');
        }
    }
}
