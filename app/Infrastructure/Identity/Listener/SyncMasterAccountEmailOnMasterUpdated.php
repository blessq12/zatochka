<?php

namespace App\Infrastructure\Identity\Listener;

use App\Application\Identity\Command\SyncMasterAccountEmailCommand;
use App\Application\Identity\Command\SyncMasterAccountEmailHandler;
use App\Domain\CRM\Event\MasterUpdated;

final class SyncMasterAccountEmailOnMasterUpdated
{
    public function __construct(
        private SyncMasterAccountEmailHandler $sync,
    ) {}

    public function handle(MasterUpdated $event): void
    {
        $this->sync->handle(new SyncMasterAccountEmailCommand(
            $event->masterId->value,
            $event->email,
        ));
    }
}
