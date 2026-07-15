<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Identity\Repository\EmployeeRepository;
use App\Domain\Identity\Repository\RoleRepository;
use App\Shared\ValueObject\EntityId;

final readonly class AssignRoleHandler
{
    public function __construct(
        private EmployeeRepository $employees,
        private RoleRepository $roles,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AssignRoleCommand $command): void
    {
        $employee = $this->employees->getById(new EntityId($command->employeeId));
        $role = $this->roles->getById(new EntityId($command->roleId));
        $employee->assignRole($role);
        $this->employees->save($employee);
        $this->events->publish($employee->pullDomainEvents());
    }
}
