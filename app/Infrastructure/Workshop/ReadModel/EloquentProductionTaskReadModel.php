<?php

namespace App\Infrastructure\Workshop\ReadModel;

use App\Application\Workshop\DTO\ProductionTaskDTO;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Workshop\Mapper\ProductionTaskMapper;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;

final readonly class EloquentProductionTaskReadModel implements ProductionTaskReadPort
{
    public function __construct(
        private ProductionTaskMapper $mapper,
    ) {}

    public function findById(int $productionTaskId): ?ProductionTaskDTO
    {
        $model = ProductionTaskModel::query()
            ->with(['diagnosis', 'workExecution'])
            ->find($productionTaskId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function listQueued(): array
    {
        return ProductionTaskModel::query()
            ->with(['diagnosis', 'workExecution'])
            ->where('status', ProductionStatus::Queued->value)
            ->get()
            ->map(fn ($model) => $this->mapper->toDTO($model))
            ->all();
    }
}
