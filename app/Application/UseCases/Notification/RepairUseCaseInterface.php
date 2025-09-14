<?php

namespace App\Application\UseCases\Notification;

interface NotificationUseCaseInterface
{
    public function loadData(array $data): self;
    public function validate(): self;
    public function execute(): mixed;
}
