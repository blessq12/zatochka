<?php

namespace App\Domain\Catalog\Exception;

use RuntimeException;

final class BranchNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Филиал #{$id} не найден.");
    }
}
