<?php

namespace App\Domain\Notification\Mapper;

use App\Domain\Notification\Entity\Notification;

interface NotificationMapper
{
    public function toDomain($eloquentModel): Notification;
    public function toEloquent(Notification $domainEntity): array;
}
