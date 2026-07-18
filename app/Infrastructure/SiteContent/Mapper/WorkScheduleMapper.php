<?php

namespace App\Infrastructure\SiteContent\Mapper;

use App\Domain\SiteContent\Entity\ScheduleDay;
use App\Domain\SiteContent\Entity\WorkSchedule;
use App\Infrastructure\SiteContent\Model\ScheduleDayModel;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Collection;

final class WorkScheduleMapper
{
    /** @param Collection<int, ScheduleDayModel> $models */
    public function toDomain(Collection $models): WorkSchedule
    {
        $days = $models
            ->sortBy('sort_order')
            ->values()
            ->map(static fn (ScheduleDayModel $model): ScheduleDay => ScheduleDay::reconstitute(
                new EntityId((int) $model->id),
                (string) $model->name,
                (bool) $model->is_day_off,
                $model->day_off_text,
                $model->workshop,
                $model->delivery,
                (int) $model->sort_order,
            ))
            ->all();

        return WorkSchedule::reconstitute($days);
    }

    /** @return list<array<string, mixed>> */
    public function toPersistence(WorkSchedule $schedule): array
    {
        $rows = [];

        foreach ($schedule->days() as $day) {
            $rows[] = [
                'id' => $day->id()->value,
                'name' => $day->name(),
                'is_day_off' => $day->isDayOff(),
                'day_off_text' => $day->dayOffText(),
                'workshop' => $day->workshop(),
                'delivery' => $day->delivery(),
                'sort_order' => $day->sortOrder(),
            ];
        }

        return $rows;
    }
}
