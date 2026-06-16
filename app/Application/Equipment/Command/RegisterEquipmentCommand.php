<?php

namespace App\Application\Equipment\Command;

final readonly class RegisterEquipmentCommand
{
    /**
     * @param  list<string>  $serialNumbers
     */
    public function __construct(
        public string $name,
        public array $serialNumbers = [],
        public ?string $brand = null,
        public ?string $model = null,
    ) {}
}
