<?php

namespace App\Application\Identity\Query;

final readonly class GetEmployeeByIdQuery
{
    public function __construct(
        public int $employeeId,
    ) {}
}
