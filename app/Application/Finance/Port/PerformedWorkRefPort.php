<?php

namespace App\Application\Finance\Port;

interface PerformedWorkRefPort
{
    public function findById(int $performedWorkId): ?PerformedWorkRefDTO;
}
