<?php

namespace App\Application\Company\Command;

final readonly class SaveBranchCommand
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $address,
        public ?string $phone,
        public bool $isActive,
    ) {}
}
