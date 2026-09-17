<?php

namespace App\Application\Workshop\Command;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Shared\Domain\ForbiddenException;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\WorkshopWorksCompleted;

final readonly class CompleteWorkshopJobHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
        private EventBus $events,
    ) {}

    public function handle(int $jobId, int $masterId): ?WorkshopJobResponse
    {
        $job = $this->jobs->findById($jobId);
        if ($job === null) {
            return null;
        }

        if ($job->masterId() !== $masterId) {
            throw new ForbiddenException('Forbidden.');
        }

        $job->complete();
        $job = $this->jobs->save($job);

        $this->events->publish(new WorkshopWorksCompleted($job->orderId()));

        return $this->assembler->assemble($job);
    }
}
