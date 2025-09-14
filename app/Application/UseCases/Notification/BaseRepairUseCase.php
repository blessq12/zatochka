<?php

namespace App\Application\UseCases\Notification;

use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\Repository\ToolRepository;
use App\Domain\Notification\Repository\ToolTypeRepository;
use App\Domain\Notification\Repository\EquipmentTypeRepository;
use App\Domain\Notification\Mapper\NotificationMapper;
use App\Domain\Notification\Mapper\ToolMapper;
use App\Domain\Notification\Mapper\ToolTypeMapper;
use App\Domain\Notification\Mapper\EquipmentTypeMapper;

abstract class BaseNotificationUseCase implements NotificationUseCaseInterface
{
    protected array $data;

    // Все репозитории домена
    protected NotificationRepository $repairRepository;
    protected ToolRepository $toolRepository;
    protected ToolTypeRepository $toolTypeRepository;
    protected EquipmentTypeRepository $equipmentTypeRepository;

    // Все мапперы домена
    protected NotificationMapper $repairMapper;
    protected ToolMapper $toolMapper;
    protected ToolTypeMapper $toolTypeMapper;
    protected EquipmentTypeMapper $equipmentTypeMapper;

    public function __construct()
    {
        // Подтягиваем ВСЕ зависимости домена
        $this->repairRepository = app(NotificationRepository::class);
        $this->toolRepository = app(ToolRepository::class);
        $this->toolTypeRepository = app(ToolTypeRepository::class);
        $this->equipmentTypeRepository = app(EquipmentTypeRepository::class);

        $this->repairMapper = app(NotificationMapper::class);
        $this->toolMapper = app(ToolMapper::class);
        $this->toolTypeMapper = app(ToolTypeMapper::class);
        $this->equipmentTypeMapper = app(EquipmentTypeMapper::class);
    }

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
