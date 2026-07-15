<?php

namespace App\Infrastructure\Identity\ReadModel;

use App\Application\Identity\DTO\EmployeeDTO;
use App\Application\Identity\ReadPort\EmployeeReadPort;
use App\Infrastructure\Identity\Mapper\EmployeeMapper;
use App\Infrastructure\Identity\Model\EmployeeModel;

final readonly class EloquentEmployeeReadModel implements EmployeeReadPort
{
    public function __construct(
        private EmployeeMapper $mapper,
    ) {}

    public function findById(int $employeeId): ?EmployeeDTO
    {
        $model = EmployeeModel::query()->with('roles')->find($employeeId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }
}
