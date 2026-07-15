<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\Employee;
use App\Domain\Identity\Repository\EmployeeRepository;
use App\Infrastructure\Identity\Mapper\EmployeeMapper;
use App\Infrastructure\Identity\Model\EmployeeModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentEmployeeRepository implements EmployeeRepository
{
    public function __construct(
        private EmployeeMapper $mapper,
    ) {}

    public function save(Employee $employee): void
    {
        DB::transaction(function () use ($employee): void {
            $model = EmployeeModel::query()->find($employee->id()->value);
            $model = $this->mapper->toPersistence($employee, $model);
            $model->save();

            $roleIds = array_map(
                static fn ($role) => $role->id()->value,
                $employee->roles(),
            );
            $model->roles()->sync($roleIds);
        });
    }

    public function findById(EntityId $id): ?Employee
    {
        $model = EmployeeModel::query()->with('roles.permissions')->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Employee
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Employee %d not found.', $id->value));
    }

    public function findByEmail(Email $email): ?Employee
    {
        $model = EmployeeModel::query()->with('roles.permissions')->where('email', $email->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
