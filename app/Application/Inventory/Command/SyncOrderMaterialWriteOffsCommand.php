<?php

namespace App\Application\Inventory\Command;

final readonly class SyncOrderMaterialWriteOffsCommand
{
    /**
     * @param  list<SyncOrderMaterialWriteOffItem>  $lines
     */
    public function __construct(
        public string $orderId,
        public array $lines,
    ) {}
}
