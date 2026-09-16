<?php

namespace App\Application\CRM\ReadPort;

use App\Application\CRM\DTO\ClientEquipmentDTO;

interface EquipmentReadPort
{
    public function findById(int $equipmentId): ?ClientEquipmentDTO;

    /** @return list<ClientEquipmentDTO> */
    public function listByClientId(int $clientId): array;

    /**
     * @return array{items: list<ClientEquipmentDTO>, meta: array{total:int,page:int,per_page:int}}
     */
    public function search(?string $query, int $page = 1, int $perPage = 20): array;
}
