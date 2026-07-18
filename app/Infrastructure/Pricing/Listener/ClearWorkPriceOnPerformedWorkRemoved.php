<?php

namespace App\Infrastructure\Pricing\Listener;

use App\Application\Pricing\Command\ClearWorkPriceForPerformedWorkCommand;
use App\Application\Pricing\Command\ClearWorkPriceForPerformedWorkHandler;
use App\Domain\Workshop\Event\PerformedWorkRemoved;

final readonly class ClearWorkPriceOnPerformedWorkRemoved
{
    public function __construct(
        private ClearWorkPriceForPerformedWorkHandler $clearWorkPrice,
    ) {}

    public function handle(PerformedWorkRemoved $event): void
    {
        $this->clearWorkPrice->handle(new ClearWorkPriceForPerformedWorkCommand(
            $event->performedWorkId->value,
        ));
    }
}
