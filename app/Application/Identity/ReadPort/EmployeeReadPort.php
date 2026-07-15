<?php

namespace App\Application\Identity\ReadPort;

use App\Application\Identity\DTO\EmployeeDTO;

interface EmployeeReadPort
{
    public function findById(int $employeeId): ?EmployeeDTO;
}
