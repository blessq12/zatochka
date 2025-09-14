<?php

namespace App\Application\UseCases\Notification\TelegramMessage;

use App\Application\UseCases\Notification\BaseNotificationUseCase;

class UpdateTelegramMessageUseCase extends BaseNotificationUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement update logic
        return $this->data;
    }
}
