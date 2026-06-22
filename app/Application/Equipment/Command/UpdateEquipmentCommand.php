<?php

namespace App\Application\Equipment\Command;

final readonly class UpdateEquipmentCommand
{
    /**
     * @param  array<string, string>  $serialNumbers
     */
    public function __construct(
        public int $equipmentId,
        public string $name,
        public array $serialNumbers = [],
        public ?string $brand = null,
        public ?string $model = null,
    ) {}
}
