<?php

namespace App\Application\Workshop\ReadPort;

use App\Application\Workshop\DTO\MasterFunnelCountsDTO;
use App\Application\Workshop\DTO\MasterProductionTaskCardDTO;
use App\Application\Workshop\DTO\ProductionTaskDTO;

interface ProductionTaskReadPort
{
    public function findById(int $productionTaskId): ?ProductionTaskDTO;

    /** @return list<ProductionTaskDTO> */
    public function listQueued(): array;

    public function findCardById(int $productionTaskId): ?MasterProductionTaskCardDTO;

    /**
     * @return array{items: list<MasterProductionTaskCardDTO>, meta: array{total:int,page:int,per_page:int}}
     */
    public function listForMasterFunnel(int $masterId, string $funnel, int $page = 1, int $perPage = 20): array;

    public function countsForMaster(int $masterId): MasterFunnelCountsDTO;
}
