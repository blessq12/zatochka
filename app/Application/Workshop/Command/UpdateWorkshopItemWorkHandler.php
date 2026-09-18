<?php

namespace App\Application\Workshop\Command;

use App\Application\Workshop\Assembler\WorkshopJobResponseAssembler;
use App\Application\Workshop\DTO\WorkshopJobResponse;
use App\Application\Workshop\Service\RepairModuleGuard;
use App\Domain\Workshop\Repository\WorkshopJobRepository;
use App\Shared\Domain\ForbiddenException;

final readonly class UpdateWorkshopItemWorkHandler
{
    public function __construct(
        private WorkshopJobRepository $jobs,
        private WorkshopJobResponseAssembler $assembler,
        private RepairModuleGuard $repairModules,
    ) {}

    /**
     * @param  list<array{title: string, equipment_module_id?: int|null}>  $works
     */
    public function handle(
        int $jobId,
        int $orderItemId,
        int $masterId,
        ?int $completedQty,
        array $works,
        ?int $maxQty = null,
    ): ?WorkshopJobResponse {
        $job = $this->jobs->findById($jobId);
        if ($job === null) {
            return null;
        }

        if ($job->masterId() !== $masterId) {
            throw new ForbiddenException('Forbidden.');
        }

        $this->repairModules->assertWorksMatchItem($job->orderId(), $orderItemId, $works);

        $job->updateItemWork($orderItemId, $completedQty, $works, $maxQty);

        return $this->assembler->assemble($this->jobs->save($job));
    }
}
