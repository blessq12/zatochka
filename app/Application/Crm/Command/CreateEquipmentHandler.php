<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Assembler\EquipmentResponseAssembler;
use App\Application\Crm\DTO\EquipmentResponse;
use App\Domain\Crm\Aggregate\Equipment;
use App\Domain\Crm\Entity\EquipmentModule;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\EquipmentRepository;
use App\Shared\Domain\DomainException;

final readonly class CreateEquipmentHandler
{
    public function __construct(
        private EquipmentRepository $equipments,
        private ClientRepository $clients,
        private EquipmentResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array{name: string, serial_number: string}>  $modules
     */
    public function handle(
        int $clientId,
        string $name,
        string $brand,
        string $type,
        array $modules = [],
    ): EquipmentResponse {
        if ($this->clients->findById($clientId) === null) {
            throw new DomainException('Client not found.');
        }

        $equipment = Equipment::create(
            $clientId,
            $name,
            $brand,
            $type,
            $this->mapModules($modules),
        );

        $equipment = $this->equipments->save($equipment);

        return $this->assembler->assemble($equipment);
    }

    /**
     * @param  list<array{name: string, serial_number: string}>  $modules
     * @return list<EquipmentModule>
     */
    private function mapModules(array $modules): array
    {
        return array_map(
            static fn (array $module): EquipmentModule => EquipmentModule::create(
                $module['name'],
                $module['serial_number'],
            ),
            $modules,
        );
    }
}
