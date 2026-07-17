<?php

namespace App\Application\Equipment\ReadPort;

/**
 * Cross-BC read: история производственных задач по оборудованию.
 * Реализация живёт на стороне Workshop (владелец данных о задачах).
 */
interface EquipmentOrderHistoryPort
{
    /**
     * @return list<array<string, mixed>>
     */
    public function historyForEquipment(int $equipmentId): array;
}
