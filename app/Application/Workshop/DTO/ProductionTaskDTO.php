<?php

namespace App\Application\Workshop\DTO;

final readonly class ProductionTaskDTO
{
    public function __construct(
        public int $id,
        public string $orderId,
        public string $status,
        public ?int $masterId,
        public ?int $diagnosisId,
        public ?int $workExecutionId,
    ) {}
}
