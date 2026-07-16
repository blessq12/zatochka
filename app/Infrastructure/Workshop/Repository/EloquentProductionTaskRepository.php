<?php

namespace App\Infrastructure\Workshop\Repository;

use App\Domain\Order\VO\OrderId;
use App\Domain\Workshop\Entity\ProductionTask;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Domain\Workshop\VO\ProductionStatus;
use App\Infrastructure\Workshop\Mapper\ProductionTaskMapper;
use App\Infrastructure\Workshop\Model\DiagnosisModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Infrastructure\Workshop\Model\WorkExecutionModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentProductionTaskRepository implements ProductionTaskRepository
{
    public function __construct(
        private ProductionTaskMapper $mapper,
    ) {}

    public function save(ProductionTask $task): void
    {
        DB::transaction(function () use ($task): void {
            $model = ProductionTaskModel::query()->find($task->id()->value);
            $model = $this->mapper->toPersistence($task, $model);
            $model->save();

            DiagnosisModel::query()->where('production_task_id', $task->id()->value)->delete();
            WorkExecutionModel::query()->where('production_task_id', $task->id()->value)->delete();

            $this->mapper->diagnosisToPersistence($task)?->save();
            $this->mapper->workToPersistence($task)?->save();

            $keepIds = [];

            foreach ($this->mapper->worksToPersistence($task) as $row) {
                $keepIds[] = (int) $row->id;

                PerformedWorkModel::query()->updateOrCreate(
                    ['id' => $row->id],
                    [
                        'production_task_id' => $row->production_task_id,
                        'order_item_id' => $row->order_item_id,
                        'equipment_component_id' => $row->equipment_component_id,
                        'master_id' => $row->master_id,
                        'description' => $row->description,
                        'created_at' => $row->created_at,
                    ],
                );
            }

            $deleteQuery = PerformedWorkModel::query()
                ->where('production_task_id', $task->id()->value);

            if ($keepIds !== []) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }

            $deleteQuery->delete();
        });
    }

    public function findById(EntityId $id): ?ProductionTask
    {
        $model = ProductionTaskModel::query()
            ->with(['diagnosis', 'workExecution', 'performedWorks'])
            ->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): ProductionTask
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Production task %d not found.', $id->value));
    }

    public function findByOrderId(OrderId $orderId): ?ProductionTask
    {
        $model = ProductionTaskModel::query()
            ->with(['diagnosis', 'workExecution', 'performedWorks'])
            ->where('order_id', $orderId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function listQueued(): array
    {
        return ProductionTaskModel::query()
            ->with(['diagnosis', 'workExecution', 'performedWorks'])
            ->where('status', ProductionStatus::Queued->value)
            ->get()
            ->map(fn ($model) => $this->mapper->toDomain($model))
            ->all();
    }
}
