<?php

namespace App\Application\UseCases\Notification\TelegramChat;

use App\Application\UseCases\Notification\BaseNotificationUseCase;

class CreateTelegramChatUseCase extends BaseNotificationUseCase
{
    public function validateSpecificData(): self
    {
        // TODO: Add validation logic
        return $this;
    }

    public function execute(): mixed
    {
        // TODO: Implement create logic
        return $this->data;
    }
}
