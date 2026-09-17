<?php

namespace App\Http\Controllers\Workshop;

use App\Application\Workshop\Command\AcceptWorkshopJobHandler;
use App\Application\Workshop\Command\CompleteWorkshopJobHandler;
use App\Application\Workshop\Command\UpdateWorkshopItemWorkHandler;
use App\Application\Workshop\Query\GetWorkshopJobByOrderHandler;
use App\Application\Workshop\Query\GetWorkshopJobHandler;
use App\Application\Workshop\Query\ListOpenWorkshopJobsHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WorkshopJobController extends Controller
{
    public function __construct(
        private AcceptWorkshopJobHandler $acceptJob,
        private UpdateWorkshopItemWorkHandler $updateItemWork,
        private CompleteWorkshopJobHandler $completeJob,
        private GetWorkshopJobHandler $getJob,
        private ListOpenWorkshopJobsHandler $listOpenJobs,
        private GetWorkshopJobByOrderHandler $getByOrder,
    ) {}

    public function accept(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'order_item_ids' => ['required', 'array', 'min:1'],
            'order_item_ids.*' => ['integer', 'min:1'],
        ]);

        $masterId = (int) $request->attributes->get('actor_id');
        $job = $this->acceptJob->handle(
            (int) $data['order_id'],
            $masterId,
            array_map('intval', $data['order_item_ids']),
        );

        return response()->json($job->toArray(), 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $masterId = (int) $request->attributes->get('actor_id');
        $items = $this->listOpenJobs->handle($masterId);

        return response()->json([
            'data' => array_map(static fn ($item) => $item->toArray(), $items),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $asMasterId = $request->attributes->get('actor_type') === 'masters'
            ? (int) $request->attributes->get('actor_id')
            : null;

        $job = $this->getJob->handle($id, $asMasterId);
        if ($job === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($job->toArray());
    }

    public function byOrder(int $orderId): JsonResponse
    {
        $job = $this->getByOrder->handle($orderId);
        if ($job === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($job->toArray());
    }

    public function updateItem(Request $request, int $id, int $orderItemId): JsonResponse
    {
        $data = $request->validate([
            'completed_qty' => ['nullable', 'integer', 'min:0'],
            'max_qty' => ['nullable', 'integer', 'min:0'],
            'works' => ['required', 'array'],
            'works.*.title' => ['required', 'string', 'max:255'],
        ]);

        $titles = array_map(
            static fn (array $work): string => (string) $work['title'],
            $data['works'],
        );

        $masterId = (int) $request->attributes->get('actor_id');
        $job = $this->updateItemWork->handle(
            $id,
            $orderItemId,
            $masterId,
            array_key_exists('completed_qty', $data) ? ($data['completed_qty'] !== null ? (int) $data['completed_qty'] : null) : null,
            $titles,
            array_key_exists('max_qty', $data) ? ($data['max_qty'] !== null ? (int) $data['max_qty'] : null) : null,
        );

        if ($job === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($job->toArray());
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $masterId = (int) $request->attributes->get('actor_id');
        $job = $this->completeJob->handle($id, $masterId);
        if ($job === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($job->toArray());
    }
}
