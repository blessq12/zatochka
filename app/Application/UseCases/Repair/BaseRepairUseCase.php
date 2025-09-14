<?php

namespace App\Application\UseCases\Repair;

use App\Domain\Repair\Repository\RepairRepository;
use App\Domain\Repair\Repository\ToolRepository;
use App\Domain\Repair\Repository\ToolTypeRepository;
use App\Domain\Repair\Repository\EquipmentTypeRepository;
use App\Domain\Repair\Mapper\RepairMapper;
use App\Domain\Repair\Mapper\ToolMapper;
use App\Domain\Repair\Mapper\ToolTypeMapper;
use App\Domain\Repair\Mapper\EquipmentTypeMapper;

abstract class BaseRepairUseCase implements RepairUseCaseInterface
{
    protected array $data;

    // Все репозитории домена
    protected RepairRepository $repairRepository;
    protected ToolRepository $toolRepository;
    protected ToolTypeRepository $toolTypeRepository;
    protected EquipmentTypeRepository $equipmentTypeRepository;

    // Все мапперы домена
    protected RepairMapper $repairMapper;
    protected ToolMapper $toolMapper;
    protected ToolTypeMapper $toolTypeMapper;
    protected EquipmentTypeMapper $equipmentTypeMapper;

    public function __construct()
    {
        // Подтягиваем ВСЕ зависимости домена
        $this->repairRepository = app(RepairRepository::class);
        $this->toolRepository = app(ToolRepository::class);
        $this->toolTypeRepository = app(ToolTypeRepository::class);
        $this->equipmentTypeRepository = app(EquipmentTypeRepository::class);

        $this->repairMapper = app(RepairMapper::class);
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
