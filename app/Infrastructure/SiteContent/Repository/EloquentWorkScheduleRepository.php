<?php

namespace App\Infrastructure\SiteContent\Repository;

use App\Domain\SiteContent\Entity\WorkSchedule;
use App\Domain\SiteContent\Repository\WorkScheduleRepository;
use App\Infrastructure\SiteContent\Mapper\WorkScheduleMapper;
use App\Infrastructure\SiteContent\Model\ScheduleDayModel;
use Illuminate\Support\Facades\DB;

final readonly class EloquentWorkScheduleRepository implements WorkScheduleRepository
{
    public function __construct(
        private WorkScheduleMapper $mapper,
    ) {}

    public function get(): WorkSchedule
    {
        return $this->mapper->toDomain(ScheduleDayModel::query()->orderBy('sort_order')->get());
    }

    public function save(WorkSchedule $schedule): void
    {
        DB::transaction(function () use ($schedule): void {
            $rows = $this->mapper->toPersistence($schedule);
            $ids = array_column($rows, 'id');

            if ($ids === []) {
                ScheduleDayModel::query()->delete();
            } else {
                ScheduleDayModel::query()->whereNotIn('id', $ids)->delete();
            }

            foreach ($rows as $row) {
                ScheduleDayModel::query()->updateOrCreate(
                    ['id' => $row['id']],
                    $row,
                );
            }
        });
    }
}
