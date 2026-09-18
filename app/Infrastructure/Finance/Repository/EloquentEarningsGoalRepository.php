<?php

namespace App\Infrastructure\Finance\Repository;

use App\Domain\Finance\Aggregate\EarningsGoal;
use App\Domain\Finance\EarningsGoalStatus;
use App\Domain\Finance\Repository\EarningsGoalRepository;
use App\Infrastructure\Finance\Eloquent\EarningsGoalModel;
use DateTimeImmutable;

final class EloquentEarningsGoalRepository implements EarningsGoalRepository
{
    public function save(EarningsGoal $goal): EarningsGoal
    {
        /** @var EarningsGoalModel $model */
        $model = $goal->id() === null
            ? new EarningsGoalModel()
            : EarningsGoalModel::query()->findOrFail($goal->id());

        $model->title = $goal->title();
        $model->target_amount = $goal->targetAmount();
        $model->starts_at = $goal->startsAt()->format('Y-m-d');
        $model->ends_at = $goal->endsAt()->format('Y-m-d H:i:s');
        $model->status = $goal->status()->value;
        $model->save();

        if ($goal->id() === null) {
            $goal->assignId((int) $model->id);
        }

        return $goal;
    }

    public function findById(int $id): ?EarningsGoal
    {
        /** @var EarningsGoalModel|null $model */
        $model = EarningsGoalModel::query()->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function all(): array
    {
        return EarningsGoalModel::query()
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (EarningsGoalModel $model): EarningsGoal => $this->toDomain($model))
            ->all();
    }

    private function toDomain(EarningsGoalModel $model): EarningsGoal
    {
        return new EarningsGoal(
            (int) $model->id,
            (string) $model->target_amount,
            new DateTimeImmutable($model->starts_at->format('Y-m-d').' 00:00:00'),
            new DateTimeImmutable($model->ends_at->format('Y-m-d H:i:s')),
            EarningsGoalStatus::from((string) $model->status),
            $model->title !== null ? (string) $model->title : null,
        );
    }
}
