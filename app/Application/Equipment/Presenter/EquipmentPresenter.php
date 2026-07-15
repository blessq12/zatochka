<?php

namespace App\Application\Equipment\Presenter;

use App\Application\Equipment\DTO\ClientEquipmentDTO;

interface EquipmentPresenter
{
    /** @return array<string, mixed> */
    public function present(ClientEquipmentDTO $equipment): array;

    /**
     * @param list<ClientEquipmentDTO> $items
     * @return list<array<string, mixed>>
     */
    public function presentCollection(array $items): array;
}
