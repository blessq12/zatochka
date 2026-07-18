<?php

namespace App\Domain\SiteContent\Repository;

use App\Domain\SiteContent\Entity\WorkSchedule;

interface WorkScheduleRepository
{
    public function get(): WorkSchedule;

    public function save(WorkSchedule $schedule): void;
}
