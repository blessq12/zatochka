<?php

namespace App\Application\Identity\Query;

use App\Application\Identity\DTO\EmployeeDTO;
use App\Application\Identity\ReadPort\EmployeeReadPort;

final readonly class GetEmployeeByIdHandler
{
    public function __construct(
        private EmployeeReadPort $readPort,
    ) {}

    public function handle(GetEmployeeByIdQuery $query): ?EmployeeDTO
    {
        return $this->readPort->findById($query->employeeId);
    }
}
