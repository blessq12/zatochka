<?php

namespace App\Infrastructure\Identity\Listener;

use App\Application\Identity\Command\SyncManagerAccountEmailCommand;
use App\Application\Identity\Command\SyncManagerAccountEmailHandler;
use App\Domain\CRM\Event\ManagerUpdated;

final class SyncManagerAccountEmailOnManagerUpdated
{
    public function __construct(
        private SyncManagerAccountEmailHandler $sync,
    ) {}

    public function handle(ManagerUpdated $event): void
    {
        $this->sync->handle(new SyncManagerAccountEmailCommand(
            $event->managerId->value,
            $event->email,
        ));
    }
}
