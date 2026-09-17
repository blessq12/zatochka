<?php

namespace App\Application\Workshop\Command;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Aggregate\WorkshopJob;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Shared\Domain\DomainException;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\OrderAcceptedIntoWork;

final readonly class AcceptWorkshopJobHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
        private EventBus $events,
    ) {}

    /**
     * @param  list<int>  $orderItemIds
     */
    public function handle(int $orderId, int $masterId, array $orderItemIds): WorkshopJobResponse
    {
        if ($this->jobs->findByOrderId($orderId) !== null) {
            throw new DomainException('Workshop job already exists for this order.');
        }

        $job = WorkshopJob::accept($orderId, $masterId, $orderItemIds);
        $job = $this->jobs->save($job);

        $this->events->publish(new OrderAcceptedIntoWork($orderId, $masterId));

        return $this->assembler->assemble($job);
    }
}
