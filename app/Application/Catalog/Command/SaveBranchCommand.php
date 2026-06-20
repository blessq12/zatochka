<?php

namespace App\Application\Catalog\Command;

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
