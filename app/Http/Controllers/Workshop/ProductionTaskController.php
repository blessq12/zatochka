<?php

namespace App\Http\Controllers\Workshop;

use App\Application\Order\Command\RejectOrderItemUnitsCommand;
use App\Application\Order\Command\RejectOrderItemUnitsHandler;
use App\Application\Workshop\Command\AddMasterCommentCommand;
use App\Application\Workshop\Command\AddMasterCommentHandler;
use App\Application\Workshop\Command\AddMasterWorkCommand;
use App\Application\Workshop\Command\AddMasterWorkHandler;
use App\Application\Workshop\Command\AssignMasterCommand;
use App\Application\Workshop\Command\AssignMasterHandler;
use App\Application\Workshop\Command\CompleteDiagnosisCommand;
use App\Application\Workshop\Command\CompleteDiagnosisHandler;
use App\Application\Workshop\Command\CompleteProductionCommand;
use App\Application\Workshop\Command\CompleteProductionHandler;
use App\Application\Workshop\Command\CompleteWorkCommand;
use App\Application\Workshop\Command\CompleteWorkHandler;
use App\Application\Workshop\Command\FinishProductionTaskCommand;
use App\Application\Workshop\Command\FinishProductionTaskHandler;
use App\Application\Workshop\Command\PauseForPartsCommand;
use App\Application\Workshop\Command\PauseForPartsHandler;
use App\Application\Workshop\Command\RemoveMasterCommentCommand;
use App\Application\Workshop\Command\RemoveMasterCommentHandler;
use App\Application\Workshop\Command\ResumeFromPartsCommand;
use App\Application\Workshop\Command\ResumeFromPartsHandler;
use App\Application\Workshop\Command\StartWorkCommand;
use App\Application\Workshop\Command\StartWorkHandler;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use App\Shared\Domain\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionTaskController extends Controller
{
    public function __construct(
        private AssignMasterHandler $assignMaster,
        private CompleteDiagnosisHandler $completeDiagnosis,
        private RejectOrderItemUnitsHandler $rejectOrderItemUnits,
        private StartWorkHandler $startWork,
        private CompleteWorkHandler $completeWork,
        private CompleteProductionHandler $completeProduction,
        private PauseForPartsHandler $pauseForParts,
        private ResumeFromPartsHandler $resumeFromParts,
        private FinishProductionTaskHandler $finishProductionTask,
        private AddMasterCommentHandler $addMasterComment,
        private AddMasterWorkHandler $addMasterWork,
        private RemoveMasterCommentHandler $removeMasterComment,
        private ProductionTaskReadPort $readPort,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'funnel' => ['nullable', 'string', 'in:new,active,waiting_parts,completed'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $funnel = $data['funnel'] ?? 'new';
        $result = $this->readPort->listForMasterFunnel(
            (int) $request->user()->id,
            $funnel,
            (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 20),
        );

        return response()->json([
            'data' => $this->serialize($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function indexQueued(): JsonResponse
    {
        return $this->ok($this->readPort->listQueued());
    }

    public function counts(Request $request): JsonResponse
    {
        return $this->ok($this->readPort->countsForMaster((int) $request->user()->id));
    }

    public function stats(Request $request): JsonResponse
    {
        $counts = $this->readPort->countsForMaster((int) $request->user()->id);

        return $this->ok([
            'counts' => [
                'new' => $counts->new,
                'active' => $counts->active,
                'waiting_parts' => $counts->waitingParts,
                'completed' => $counts->completed,
            ],
            'avg_work_duration_seconds' => null,
        ]);
    }

    public function show(int $productionTaskId): JsonResponse
    {
        $task = $this->readPort->findCardById($productionTaskId);

        if ($task === null) {
            return response()->json(['message' => 'Production task not found.'], 404);
        }

        $this->assertOwnedByMaster($task->masterId, (int) request()->user()->id);

        return $this->ok($task);
    }

    public function assignMaster(Request $request, int $productionTaskId): JsonResponse
    {
        $data = $request->validate([
            'masterId' => ['required', 'integer'],
        ]);

        $this->assignMaster->handle(new AssignMasterCommand($productionTaskId, (int) $data['masterId']));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function completeDiagnosis(Request $request, int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);

        $data = $request->validate([
            'summary' => ['required', 'string'],
            'technicalNotes' => ['nullable', 'string'],
        ]);

        $this->completeDiagnosis->handle(new CompleteDiagnosisCommand(
            $productionTaskId,
            $this->ids->next('diagnosis')->value,
            $data['summary'],
            $data['technicalNotes'] ?? null,
        ));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function reject(Request $request, int $productionTaskId): JsonResponse
    {
        $card = $this->assertTaskOwned($productionTaskId);

        $data = $request->validate([
            'orderItemId' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
        ]);

        $this->rejectOrderItemUnits->handle(new RejectOrderItemUnitsCommand(
            $card->orderId,
            (int) $data['orderItemId'],
            (int) ($data['quantity'] ?? 1),
            $data['reason'],
        ));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function startWork(Request $request, int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);

        $data = $request->validate([
            'description' => ['nullable', 'string'],
        ]);

        $this->startWork->handle(new StartWorkCommand(
            $productionTaskId,
            $this->ids->next('work_execution')->value,
            $data['description'] ?? 'Взято в работу',
        ));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function pauseForParts(int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);
        $this->pauseForParts->handle(new PauseForPartsCommand($productionTaskId));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function resume(int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);
        $this->resumeFromParts->handle(new ResumeFromPartsCommand($productionTaskId));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function completeWork(int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);
        $this->completeWork->handle(new CompleteWorkCommand($productionTaskId));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function completeProduction(int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);
        $this->completeProduction->handle(new CompleteProductionCommand($productionTaskId));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function finish(int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);
        $this->finishProductionTask->handle(new FinishProductionTaskCommand($productionTaskId));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function addComment(Request $request, int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);

        $data = $request->validate([
            'text' => ['required', 'string'],
            'orderItemId' => ['prohibited'],
        ]);

        $this->addMasterComment->handle(new AddMasterCommentCommand(
            $productionTaskId,
            $this->ids->next('master_comment')->value,
            (int) $request->user()->id,
            $data['text'],
        ));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function addWork(Request $request, int $productionTaskId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);

        $data = $request->validate([
            'text' => ['required', 'string'],
            'orderItemId' => ['required', 'integer'],
        ]);

        $this->addMasterWork->handle(new AddMasterWorkCommand(
            $productionTaskId,
            $this->ids->next('master_comment')->value,
            (int) $request->user()->id,
            $data['text'],
            (int) $data['orderItemId'],
        ));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    public function removeComment(int $productionTaskId, int $commentId): JsonResponse
    {
        $this->assertTaskOwned($productionTaskId);

        $this->removeMasterComment->handle(new RemoveMasterCommentCommand(
            $productionTaskId,
            $commentId,
            (int) request()->user()->id,
        ));

        return $this->ok($this->cardOrFail($productionTaskId));
    }

    private function cardOrFail(int $productionTaskId): mixed
    {
        $card = $this->readPort->findCardById($productionTaskId);

        if ($card === null) {
            throw new DomainException('Production task not found.');
        }

        return $card;
    }

    private function assertTaskOwned(int $productionTaskId): mixed
    {
        $card = $this->readPort->findCardById($productionTaskId);

        if ($card === null) {
            throw new DomainException('Production task not found.');
        }

        $this->assertOwnedByMaster($card->masterId, (int) request()->user()->id);

        return $card;
    }

    private function assertOwnedByMaster(?int $masterId, int $authId): void
    {
        if ($masterId === null || $masterId !== $authId) {
            throw new DomainException('Production task is not assigned to the current master.');
        }
    }
}
