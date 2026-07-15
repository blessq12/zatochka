<?php

namespace App\Application\Identity\Presenter;

use App\Application\Identity\DTO\EmployeeDTO;

interface EmployeePresenter
{
    /** @return array<string, mixed> */
    public function present(EmployeeDTO $employee): array;
}
