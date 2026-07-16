<?php

namespace App\Application\Shared;

interface UnitOfWork
{
    /**
     * Run write-side work in one DB transaction (nested calls use savepoints).
     *
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function execute(callable $operation): mixed;
}
