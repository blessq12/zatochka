<?php

namespace App\Application\Crm\Assembler;

use App\Application\Crm\DTO\EquipmentResponse;
use App\Domain\Crm\Aggregate\Equipment;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\ProfileAdditionalRepository;

final readonly class EquipmentResponseAssembler
{
    public function __construct(
        private ClientRepository $clients,
        private ProfileAdditionalRepository $profiles,
    ) {}

    public function assemble(Equipment $equipment): EquipmentResponse
    {
        $clientName = null;
        $client = $this->clients->findById($equipment->clientId());
        if ($client !== null) {
            $profile = $this->profiles->findById($client->profileAdditionalId());
            $clientName = $profile?->name();
        }

        $modules = [];
        foreach ($equipment->modules() as $module) {
            $modules[] = [
                'id' => $module->id(),
                'name' => $module->name(),
                'serial_number' => $module->serialNumber(),
            ];
        }

        return new EquipmentResponse(
            (int) $equipment->id(),
            $equipment->clientId(),
            $clientName,
            $equipment->name(),
            $equipment->brand(),
            $equipment->type(),
            $modules,
        );
    }
}
