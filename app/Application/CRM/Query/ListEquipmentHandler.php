<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Assembler\EquipmentResponseAssembler;
use App\Application\Crm\DTO\EquipmentResponse;
use App\Domain\Crm\Repository\EquipmentRepository;

final readonly class ListEquipmentHandler
{
    public function __construct(
        private EquipmentRepository $equipments,
        private EquipmentResponseAssembler $assembler,
    ) {}

    /**
     * @return list<EquipmentResponse>
     */
    public function handle(?int $clientId = null): array
    {
        return array_map(
            fn ($equipment): EquipmentResponse => $this->assembler->assemble($equipment),
            $this->equipments->all($clientId),
        );
    }
}
