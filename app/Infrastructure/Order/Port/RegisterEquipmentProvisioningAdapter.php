<?php

namespace App\Infrastructure\Order\Port;

use App\Application\Equipment\Command\RegisterEquipmentCommand;
use App\Application\Equipment\Command\RegisterEquipmentHandler;
use App\Application\Order\Port\EquipmentProvisioningPort;

final readonly class RegisterEquipmentProvisioningAdapter implements EquipmentProvisioningPort
{
    public function __construct(
        private RegisterEquipmentHandler $registerEquipment,
    ) {}

    public function register(
        int $equipmentId,
        int $clientId,
        string $title,
        string $brand,
        string $modelName,
        ?string $notes = null,
    ): void {
        $this->registerEquipment->handle(new RegisterEquipmentCommand(
            $equipmentId,
            $title,
            $brand,
            $modelName,
            $clientId,
            $notes,
        ));
    }
}
