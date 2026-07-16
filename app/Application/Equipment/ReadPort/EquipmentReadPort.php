<?php

namespace App\Application\Equipment\ReadPort;

use App\Application\Equipment\DTO\ClientEquipmentDTO;

interface EquipmentReadPort
{
    public function findById(int $equipmentId): ?ClientEquipmentDTO;

    /** @return list<ClientEquipmentDTO> */
    public function listByClientId(int $clientId): array;

    /**
     * @return array{items: list<ClientEquipmentDTO>, meta: array{total:int,page:int,per_page:int}}
     */
    public function search(?string $query, int $page = 1, int $perPage = 20): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function orderHistory(int $equipmentId): array;
}
