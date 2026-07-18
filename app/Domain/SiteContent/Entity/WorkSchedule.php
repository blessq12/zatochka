<?php

namespace App\Domain\SiteContent\Entity;

final class WorkSchedule
{
    /** @param list<ScheduleDay> $days */
    private function __construct(
        private array $days,
    ) {}

    /** @param list<ScheduleDay> $days */
    public static function create(array $days = []): self
    {
        return new self(array_values($days));
    }

    /** @param list<ScheduleDay> $days */
    public static function reconstitute(array $days): self
    {
        return new self(array_values($days));
    }

    /** @return list<ScheduleDay> */
    public function days(): array
    {
        return $this->days;
    }

    /** @param list<ScheduleDay> $days */
    public function replaceDays(array $days): void
    {
        $this->days = array_values($days);
    }
}
