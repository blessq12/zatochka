<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Application\Identity\DTO\EmployeeDTO;
use App\Domain\Identity\Entity\Employee;
use App\Infrastructure\Identity\Model\EmployeeModel;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;

final class EmployeeMapper
{
    public function __construct(
        private RoleMapper $roles,
    ) {}

    public function toDomain(EmployeeModel $model): Employee
    {
        $roleAggregates = [];

        foreach ($model->roles as $roleModel) {
            $roleModel->loadMissing('permissions');
            $roleAggregates[] = $this->roles->toDomain($roleModel);
        }

        return Employee::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            new Email((string) $model->email),
            (bool) $model->active,
            $roleAggregates,
        );
    }

    public function toPersistence(Employee $employee, ?EmployeeModel $model = null): EmployeeModel
    {
        $model ??= new EmployeeModel();
        $model->id = $employee->id()->value;
        $model->name = $employee->name();
        $model->email = $employee->email()->value;
        $model->active = $employee->isActive();

        return $model;
    }

    public function toDTO(EmployeeModel $model): EmployeeDTO
    {
        $roleNames = $model->roles->pluck('name')->map(static fn ($name) => (string) $name)->all();

        return new EmployeeDTO(
            (int) $model->id,
            (string) $model->name,
            (string) $model->email,
            (bool) $model->active,
            array_values($roleNames),
        );
    }
}
