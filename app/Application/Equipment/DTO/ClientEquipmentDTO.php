<?php

namespace App\Application\Equipment\DTO;

final readonly class ClientEquipmentDTO
{
    /**
     * @param list<array{id:int,name:string,serialNumber:?string}> $components
     * @param list<array{id:int,orderItemId:int,summary:string,recordedAt:string}> $repairHistory
     */
    public function __construct(
        public int $id,
        public string $number,
        public ?int $clientId,
        public string $title,
        public string $brand,
        public string $modelName,
        public string $equipmentType,
        public array $components,
        public array $repairHistory,
    ) {}
}
