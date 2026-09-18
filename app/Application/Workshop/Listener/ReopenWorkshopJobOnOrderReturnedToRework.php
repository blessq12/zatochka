<?php

namespace App\Application\Workshop\Listener;

use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Shared\Domain\DomainException;
use App\Shared\IntegrationEvents\OrderReturnedToRework;

final readonly class ReopenWorkshopJobOnOrderReturnedToRework
{
    public function __construct(
        private WorkshopJobRepository $jobs,
    ) {}

    public function handle(OrderReturnedToRework $event): void
    {
        $job = $this->jobs->findByOrderId($event->orderId);
        if ($job === null) {
            throw new DomainException('Workshop job not found for order.');
        }

        $job->reopen();
        $this->jobs->save($job);
    }
}
