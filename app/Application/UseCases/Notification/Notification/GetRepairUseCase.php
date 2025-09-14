<?php

namespace App\Application\UseCases\Notification\Notification;

use App\Application\UseCases\Notification\BaseNotificationUseCase;

class GetNotificationUseCase extends BaseNotificationUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement get logic
        return $this->data;
    }
}
