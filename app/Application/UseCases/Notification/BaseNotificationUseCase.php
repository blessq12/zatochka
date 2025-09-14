<?php

namespace App\Application\UseCases\Notification;

use App\Application\UseCases\Notification\NotificationUseCaseInterface;


abstract class BaseNotificationUseCase implements NotificationUseCaseInterface
{
    protected array $data;

    public function __construct() {}

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        $this->validateSpecificData();
        return $this;
    }

    abstract public function validateSpecificData(): self;

    abstract public function execute(): mixed;
}
