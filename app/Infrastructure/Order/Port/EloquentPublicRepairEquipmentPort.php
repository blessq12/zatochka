<?php

namespace App\Infrastructure\Order\Port;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Order\Port\PublicRepairEquipmentPort;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Equipment\VO\EquipmentType;
use App\Shared\Domain\DomainException;

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
    ): int {
        $type = EquipmentType::tryFrom($equipmentType)
            ?? throw new DomainException('Unknown equipment type.');

        $equipmentId = $this->ids->next('equipment')->value;

        $this->registerEquipment->handle(new RegisterEquipmentCommand(
            $equipmentId,
            $deviceName,
            'Не указан',
            'Не указана',
            $type->value,
            $clientId,
            [],
        ));

        return $equipmentId;
    }
}
