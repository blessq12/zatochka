<?php

namespace App\Application\Pricing\Query;

use App\Application\Pricing\DTO\EstimateDTO;
use App\Application\Pricing\ReadPort\EstimateReadPort;

final readonly class GetEstimateByIdHandler
{
    public function __construct(
        private EstimateReadPort $readPort,
    ) {}

    public function handle(GetEstimateByIdQuery $query): ?EstimateDTO
    {
        return $this->readPort->findById($query->estimateId);
    }
}
