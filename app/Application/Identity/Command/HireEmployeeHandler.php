<?php

namespace App\Application\Identity\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Identity\Entity\Employee;
use App\Domain\Identity\Repository\EmployeeRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;

final readonly class HireEmployeeHandler
{
    public function __construct(
        private EmployeeRepository $employees,
        private DomainEventPublisher $events,
    ) {}

    public function handle(HireEmployeeCommand $command): void
    {
        $email = new Email($command->email);

        if ($this->employees->findByEmail($email) !== null) {
            throw new DomainException('Employee with this email already exists.');
        }

        $employee = Employee::hire(
            new EntityId($command->employeeId),
            $command->name,
            $email,
        );

        $this->employees->save($employee);
        $this->events->publish($employee->pullDomainEvents());
    }
}
