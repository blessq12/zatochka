<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\Employee;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;

interface EmployeeRepository
{
    public function save(Employee $employee): void;

    public function findById(EntityId $id): ?Employee;

    public function getById(EntityId $id): Employee;

    public function findByEmail(Email $email): ?Employee;
}
