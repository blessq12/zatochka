<?php

namespace App\Application\Workshop\Query;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Repository\WorkshopJobRepository;

final readonly class ListOpenWorkshopJobsHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
    ) {}

    /**
     * @return list<WorkshopJobResponse>
     */
    public function handle(int $masterId): array
    {
        return array_map(
            fn ($job): WorkshopJobResponse => $this->assembler->assemble($job),
            $this->jobs->findOpenByMasterId($masterId),
        );
    }
}
