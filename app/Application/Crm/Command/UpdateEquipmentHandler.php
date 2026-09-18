<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Assembler\EquipmentResponseAssembler;
use App\Application\Crm\DTO\EquipmentResponse;
use App\Domain\Crm\Entity\EquipmentModule;
use App\Domain\Crm\Repository\EquipmentRepository;

final readonly class UpdateEquipmentHandler
{
    public function __construct(
        private EquipmentRepository $equipments,
        private EquipmentResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<array{id?: int, name: string, serial_number: string}>  $modules
     */
    public function handle(
        int $id,
        string $name,
        string $brand,
        string $type,
        array $modules = [],
    ): ?EquipmentResponse {
        $equipment = $this->equipments->findById($id);
        if ($equipment === null) {
            return null;
        }

        $equipment->changeDetails($name, $brand, $type);
        $equipment->replaceModules(array_map(
            static function (array $module): EquipmentModule {
                $moduleId = isset($module['id']) ? (int) $module['id'] : null;
                if ($moduleId !== null && $moduleId > 0) {
                    return new EquipmentModule(
                        $moduleId,
                        $module['name'],
                        $module['serial_number'],
                    );
                }

                return EquipmentModule::create(
                    $module['name'],
                    $module['serial_number'],
                );
            },
            $modules,
        ));

        $equipment = $this->equipments->save($equipment);

        return $this->assembler->assemble($equipment);
    }
}
