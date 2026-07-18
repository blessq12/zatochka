<?php

namespace App\Infrastructure\Order\Port;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Order\Port\PublicRepairEquipmentPort;
use App\Application\Shared\EntityIdGenerator;

final readonly class EloquentPublicRepairEquipmentPort implements PublicRepairEquipmentPort
{
    public function __construct(
        private RegisterEquipmentHandler $registerEquipment,
        private EntityIdGenerator $ids,
    ) {}

    public function ensureForClient(
        int $clientId,
        string $deviceName,
        string $equipmentType,
        ?string $problemDescription = null,
    ): int {
        $equipmentId = $this->ids->next('equipment')->value;
        $typeLabel = $this->equipmentTypeLabel($equipmentType);

        $this->registerEquipment->handle(new RegisterEquipmentCommand(
            $equipmentId,
            $deviceName,
            $typeLabel,
            $equipmentType,
            $clientId,
            $problemDescription,
            [],
        ));

        return $equipmentId;
    }

    private function equipmentTypeLabel(string $equipmentType): string
    {
        return match ($equipmentType) {
            'clipper' => 'Машинка для стрижки',
            'trimmer' => 'Триммер',
            'shaver' => 'Бритва',
            'dryer' => 'Фен',
            'other' => 'Другое',
            default => $equipmentType !== '' ? $equipmentType : 'Оборудование',
        };
    }
}
