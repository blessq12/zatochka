<?php

namespace App\Application\Workshop\Query;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Repository\WorkshopJobRepository;

final readonly class GetWorkshopJobByOrderHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
    ) {}

    public function handle(int $orderId): ?WorkshopJobResponse
    {
        $job = $this->jobs->findByOrderId($orderId);
        if ($job === null) {
            return null;
        }

        return $this->assembler->assemble($job);
    }
}
