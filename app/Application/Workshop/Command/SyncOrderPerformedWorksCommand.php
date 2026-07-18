<?php

namespace App\Application\Workshop\Command;

final readonly class SyncOrderPerformedWorksCommand
{
    /**
     * @param  list<SyncOrderPerformedWorkItem>  $works
     */
    public function __construct(
        public string $orderId,
        public array $works,
    ) {}
}
