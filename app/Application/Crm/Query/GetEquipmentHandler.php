<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Assembler\EquipmentResponseAssembler;
use App\Application\Crm\DTO\EquipmentResponse;
use App\Domain\Crm\Repository\EquipmentRepository;

final readonly class GetEquipmentHandler
{
    public function __construct(
        private EquipmentRepository $equipments,
        private EquipmentResponseAssembler $assembler,
    ) {}

    public function handle(int $id): ?EquipmentResponse
    {
        $equipment = $this->equipments->findById($id);
        if ($equipment === null) {
            return null;
        }

        return $this->assembler->assemble($equipment);
    }
}
