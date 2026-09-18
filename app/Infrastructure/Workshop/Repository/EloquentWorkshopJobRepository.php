<?php

namespace App\Infrastructure\Workshop\Repository;

use App\Domain\Workshop\Aggregate\WorkshopJob;
use App\Domain\Workshop\Entity\ItemWork;
use App\Domain\Workshop\Entity\WorkEntry;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Domain\Workshop\WorkshopJobStatus;
use App\Infrastructure\Workshop\Eloquent\WorkshopItemWorkModel;
use App\Infrastructure\Workshop\Eloquent\WorkshopJobModel;
use App\Infrastructure\Workshop\Eloquent\WorkshopWorkEntryModel;
use Illuminate\Support\Facades\DB;

final class EloquentWorkshopJobRepository implements WorkshopJobRepository
{
    public function save(WorkshopJob $job): WorkshopJob
    {
        return DB::transaction(function () use ($job): WorkshopJob {
            /** @var WorkshopJobModel $model */
            $model = $job->id() === null
                ? new WorkshopJobModel()
                : WorkshopJobModel::query()->findOrFail($job->id());

            $model->order_id = $job->orderId();
            $model->master_id = $job->masterId();
            $model->status = $job->status()->value;
            $model->save();

            if ($job->id() === null) {
                $job->assignId((int) $model->id);
            }

            WorkshopItemWorkModel::query()->where('job_id', $model->id)->delete();

            foreach ($job->items() as $item) {
                $itemModel = new WorkshopItemWorkModel([
                    'job_id' => $model->id,
                    'order_item_id' => $item->orderItemId(),
                    'completed_qty' => $item->completedQty(),
                ]);
                $itemModel->save();
                $item->assignId((int) $itemModel->id);

                foreach ($item->works() as $index => $work) {
                    $workModel = new WorkshopWorkEntryModel([
                        'item_work_id' => $itemModel->id,
                        'title' => $work->title(),
                        'position' => $work->position() ?: $index,
                        'equipment_module_id' => $work->equipmentModuleId(),
                    ]);
                    $workModel->save();
                    $work->assignId((int) $workModel->id);
                }
            }

            return $job;
        });
    }

    public function findById(int $id): ?WorkshopJob
    {
        /** @var WorkshopJobModel|null $model */
        $model = WorkshopJobModel::query()->with('items.works')->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByOrderId(int $orderId): ?WorkshopJob
    {
        /** @var WorkshopJobModel|null $model */
        $model = WorkshopJobModel::query()
            ->with('items.works')
            ->where('order_id', $orderId)
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findOpenByMasterId(int $masterId): array
    {
        return WorkshopJobModel::query()
            ->with('items.works')
            ->where('master_id', $masterId)
            ->where('status', WorkshopJobStatus::Open->value)
            ->orderByDesc('id')
            ->get()
            ->map(fn (WorkshopJobModel $model): WorkshopJob => $this->toDomain($model))
            ->values()
            ->all();
    }

    private function toDomain(WorkshopJobModel $model): WorkshopJob
    {
        $items = $model->items
            ->map(static function (WorkshopItemWorkModel $item): ItemWork {
                $works = $item->works
                    ->map(static fn (WorkshopWorkEntryModel $work): WorkEntry => new WorkEntry(
                        (int) $work->id,
                        (string) $work->title,
                        (int) $work->position,
                        $work->equipment_module_id !== null ? (int) $work->equipment_module_id : null,
                    ))
                    ->values()
                    ->all();

                return new ItemWork(
                    (int) $item->id,
                    (int) $item->order_item_id,
                    $item->completed_qty !== null ? (int) $item->completed_qty : null,
                    $works,
                );
            })
            ->values()
            ->all();

        return new WorkshopJob(
            (int) $model->id,
            (int) $model->order_id,
            (int) $model->master_id,
            WorkshopJobStatus::from((string) $model->status),
            $items,
        );
    }
}
