<?php

namespace App\Domain\Identity\Service;

use App\Domain\Identity\Entity\Employee;
use App\Domain\Identity\VO\PermissionCode;
use App\Shared\Domain\DomainException;

final class AccessControlService
{
    public function assertCan(Employee $employee, PermissionCode $code): void
    {
        if (! $employee->can($code)) {
            throw new DomainException(sprintf('Permission "%s" is denied.', $code->value));
        }
    }
}
