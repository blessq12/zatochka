<?php

namespace App\Application\SiteContent\Command;

use App\Application\Shared\EntityIdGenerator;
use App\Domain\SiteContent\Entity\ScheduleDay;
use App\Domain\SiteContent\Repository\WorkScheduleRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ReplaceWorkScheduleHandler
{
    public function __construct(
        private WorkScheduleRepository $schedules,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(ReplaceWorkScheduleCommand $command): void
    {
        $days = [];

        foreach (array_values($command->days) as $index => $day) {
            $id = isset($day['id']) && $day['id'] !== null && $day['id'] !== ''
                ? new EntityId((int) $day['id'])
                : $this->ids->next('site_schedule_day');

            $isDayOff = (bool) ($day['is_day_off'] ?? false);

            $days[] = ScheduleDay::reconstitute(
                $id,
                (string) ($day['name'] ?? ''),
                $isDayOff,
                isset($day['day_off_text']) ? (string) $day['day_off_text'] : null,
                isset($day['workshop']) ? (string) $day['workshop'] : null,
                isset($day['delivery']) ? (string) $day['delivery'] : null,
                $index + 1,
            );
        }

        $schedule = $this->schedules->get();
        $schedule->replaceDays($days);
        $this->schedules->save($schedule);
    }
}
