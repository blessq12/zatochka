<?php

namespace App\Application\Workshop\DTO;

final readonly class MasterFunnelCountsDTO
{
    public function __construct(
        public int $new,
        public int $active,
        public int $waitingParts,
        public int $completed,
    ) {}
}
