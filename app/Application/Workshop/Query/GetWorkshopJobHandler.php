<?php

namespace App\Application\Workshop\Query;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Shared\Domain\ForbiddenException;

final readonly class GetWorkshopJobHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
    ) {}

    public function handle(int $id, ?int $asMasterId = null): ?WorkshopJobResponse
    {
        $job = $this->jobs->findById($id);
        if ($job === null) {
            return null;
        }

        if ($asMasterId !== null && $job->masterId() !== $asMasterId) {
            throw new ForbiddenException('Forbidden.');
        }

        return $this->assembler->assemble($job);
    }
}
