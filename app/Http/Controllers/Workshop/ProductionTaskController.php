<?php

namespace App\Http\Controllers\Workshop;

use App\Application\Workshop\Command\AddMasterCommentCommand;
use App\Application\Workshop\Command\AddMasterCommentHandler;
use App\Application\Workshop\Command\AssignMasterCommand;
use App\Application\Workshop\Command\AssignMasterHandler;
use App\Application\Workshop\Command\CompleteDiagnosisCommand;
use App\Application\Workshop\Command\CompleteDiagnosisHandler;
use App\Application\Workshop\Command\CompleteProductionCommand;
use App\Application\Workshop\Command\CompleteProductionHandler;
use App\Application\Workshop\Command\CompleteWorkCommand;
use App\Application\Workshop\Command\CompleteWorkHandler;
use App\Application\Workshop\Command\RejectElementCommand;
use App\Application\Workshop\Command\RejectElementHandler;
use App\Application\Workshop\Command\StartWorkCommand;
use App\Application\Workshop\Command\StartWorkHandler;
use App\Application\Workshop\Query\GetProductionTaskByIdHandler;
use App\Application\Workshop\Query\GetProductionTaskByIdQuery;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;
use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductionTaskController extends Controller
{
    public function __construct(
        private AssignMasterHandler $assignMaster,
        private CompleteDiagnosisHandler $completeDiagnosis,
        private RejectElementHandler $rejectElement,
        private StartWorkHandler $startWork,
        private CompleteWorkHandler $completeWork,
        private CompleteProductionHandler $completeProduction,
        private AddMasterCommentHandler $addMasterComment,
        private GetProductionTaskByIdHandler $getProductionTaskById,
        private ProductionTaskReadPort $readPort,
        private SequentialEntityIdGenerator $ids,
    ) {}

    public function indexQueued(): JsonResponse
    {
        return $this->ok($this->readPort->listQueued());
    }

    public function show(int $productionTaskId): JsonResponse
    {
        $task = $this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId));

        if ($task === null) {
            return response()->json(['message' => 'Production task not found.'], 404);
        }

        return $this->ok($task);
    }

    public function assignMaster(Request $request, int $productionTaskId): JsonResponse
    {
        $data = $request->validate([
            'masterId' => ['required', 'integer'],
        ]);

        $this->assignMaster->handle(new AssignMasterCommand($productionTaskId, (int) $data['masterId']));

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }

    public function completeDiagnosis(Request $request, int $productionTaskId): JsonResponse
    {
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

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }

    public function reject(Request $request, int $productionTaskId): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $this->rejectElement->handle(new RejectElementCommand($productionTaskId, $data['reason']));

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }

    public function startWork(Request $request, int $productionTaskId): JsonResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string'],
        ]);

        $this->startWork->handle(new StartWorkCommand(
            $productionTaskId,
            $this->ids->next('work_execution')->value,
            $data['description'],
        ));

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }

    public function completeWork(int $productionTaskId): JsonResponse
    {
        $this->completeWork->handle(new CompleteWorkCommand($productionTaskId));

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }

    public function completeProduction(int $productionTaskId): JsonResponse
    {
        $this->completeProduction->handle(new CompleteProductionCommand($productionTaskId));

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }

    public function addComment(Request $request, int $productionTaskId): JsonResponse
    {
        $data = $request->validate([
            'masterId' => ['required', 'integer'],
            'text' => ['required', 'string'],
        ]);

        $this->addMasterComment->handle(new AddMasterCommentCommand(
            $productionTaskId,
            $this->ids->next('master_comment')->value,
            (int) $data['masterId'],
            $data['text'],
        ));

        return $this->ok($this->getProductionTaskById->handle(new GetProductionTaskByIdQuery($productionTaskId)));
    }
}
