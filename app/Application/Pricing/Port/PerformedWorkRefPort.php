<?php

namespace App\Application\Pricing\Port;

interface PerformedWorkRefPort
{
    public function findById(int $performedWorkId): ?PerformedWorkRefDTO;
}
