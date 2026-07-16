<?php

namespace App\Infrastructure\Workshop\ReadModel;

use App\Application\Workshop\DTO\MasterFunnelCountsDTO;
use App\Application\Workshop\DTO\MasterProductionTaskCardDTO;
use App\Application\Workshop\DTO\ProductionTaskDTO;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Workshop\Mapper\ProductionTaskMapper;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Infrastructure\Workshop\Presenter\MasterProductionTaskPresenter;

final readonly class EloquentProductionTaskReadModel implements ProductionTaskReadPort
{
    public function __construct(
        private ProductionTaskMapper $mapper,
        private MasterProductionTaskPresenter $presenter,
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

    public function findCardById(int $productionTaskId): ?MasterProductionTaskCardDTO
    {
        $model = $this->cardQuery()->find($productionTaskId);

        return $model === null ? null : $this->presenter->present($model);
    }

    public function listForMasterFunnel(int $masterId, string $funnel, int $page = 1, int $perPage = 20): array
    {
        $statuses = ProductionStatus::forFunnel($funnel);
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        if ($statuses === []) {
            return [
                'items' => [],
                'meta' => ['total' => 0, 'page' => $page, 'per_page' => $perPage],
            ];
        }

        $query = $this->cardQuery()
            ->where('master_id', $masterId)
            ->whereIn('status', array_map(static fn (ProductionStatus $s) => $s->value, $statuses))
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $items = $query
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($model) => $this->presenter->present($model))
            ->all();

        return [
            'items' => $items,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ];
    }

    public function countsForMaster(int $masterId): MasterFunnelCountsDTO
    {
        $base = ProductionTaskModel::query()->where('master_id', $masterId);

        return new MasterFunnelCountsDTO(
            (clone $base)->whereIn('status', array_map(
                static fn (ProductionStatus $s) => $s->value,
                ProductionStatus::forFunnel('new'),
            ))->count(),
            (clone $base)->whereIn('status', array_map(
                static fn (ProductionStatus $s) => $s->value,
                ProductionStatus::forFunnel('active'),
            ))->count(),
            (clone $base)->whereIn('status', array_map(
                static fn (ProductionStatus $s) => $s->value,
                ProductionStatus::forFunnel('waiting_parts'),
            ))->count(),
            (clone $base)->whereIn('status', array_map(
                static fn (ProductionStatus $s) => $s->value,
                ProductionStatus::forFunnel('completed'),
            ))->count(),
        );
    }

    private function cardQuery()
    {
        return ProductionTaskModel::query()->with([
            'comments',
            'order.client',
            'order.items.equipment.components',
        ]);
    }
}
