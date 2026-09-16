<?php

namespace App\Domain\CRM\Repository;

use App\Domain\CRM\Entity\ClientEquipment;
use App\Shared\ValueObject\EntityId;

interface ClientEquipmentRepository
{
    public function save(ClientEquipment $equipment): void;

    public function findById(EntityId $id): ?ClientEquipment;

    public function getById(EntityId $id): ClientEquipment;

    /** @return list<ClientEquipment> */
    public function listByClientId(EntityId $clientId): array;
}
