<?php

namespace App\Application\Workshop\Command;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Shared\Domain\ForbiddenException;

final readonly class UpdateWorkshopItemWorkHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
    ) {}

    /**
     * @param  list<string>  $workTitles
     */
    public function handle(
        int $jobId,
        int $orderItemId,
        int $masterId,
        ?int $completedQty,
        array $workTitles,
        ?int $maxQty = null,
    ): ?WorkshopJobResponse {
        $job = $this->jobs->findById($jobId);
        if ($job === null) {
            return null;
        }

        if ($job->masterId() !== $masterId) {
            throw new ForbiddenException('Forbidden.');
        }

        $job->updateItemWork($orderItemId, $completedQty, $workTitles, $maxQty);

        return $this->assembler->assemble($this->jobs->save($job));
    }
}
