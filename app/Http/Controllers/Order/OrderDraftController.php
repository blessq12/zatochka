<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\CancelOrderDraftHandler;
use App\Application\Order\Command\CreateClientOrderDraftHandler;
use App\Application\Order\Command\PromoteOrderDraftHandler;
use App\Application\Order\Command\UpdateOrderDraftHandler;
use App\Application\Order\Query\GetOrderDraftHandler;
use App\Application\Order\Query\ListOrderDraftsHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderDraftController extends Controller
{
    public function __construct(
        private ListOrderDraftsHandler $listDrafts,
        private GetOrderDraftHandler $getDraft,
        private CreateClientOrderDraftHandler $createClientDraft,
        private UpdateOrderDraftHandler $updateDraft,
        private CancelOrderDraftHandler $cancelDraft,
        private PromoteOrderDraftHandler $promoteDraft,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $actorType = (string) $request->attributes->get('actor_type');
        $asClient = $actorType === 'clients';

        $clientId = null;
        $status = is_string($request->query('status')) ? $request->query('status') : null;
        $source = is_string($request->query('source')) ? $request->query('source') : null;
        $phone = is_string($request->query('phone')) ? $request->query('phone') : null;
        $statusesIn = null;

        if ($asClient) {
            $clientId = (int) $request->attributes->get('actor_id');
            $source = null;
            $phone = null;
            if ($status === null || $status === '') {
                $statusesIn = ['pending', 'promoted'];
                $status = null;
            }
        } else {
            $queryClientId = $request->query('client_id');
            $clientId = $queryClientId !== null && $queryClientId !== '' ? (int) $queryClientId : null;
        }

        $items = $this->listDrafts->handle($clientId, $status ?: null, $source ?: null, $phone ?: null, $statusesIn);

        return response()->json([
            'data' => array_map(static fn ($item) => $item->toArray(), $items),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $asClientId = $this->asClientId($request);
        $item = $this->getDraft->handle($id, $asClientId);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $clientId = (int) $request->attributes->get('actor_id');
        $data = $request->validate($this->draftRules(requireContacts: false));

        $item = $this->createClientDraft->handle(
            $clientId,
            $data['service_type'],
            $data['payload'],
            (bool) $data['needs_delivery'],
            $data['delivery_address'] ?? null,
            $data['comment'] ?? null,
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
        );

        return response()->json($item->toArray(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $asClientId = $this->asClientId($request);
        $data = $request->validate($this->draftRules(requireContacts: false));

        $item = $this->updateDraft->handle(
            $id,
            $data['service_type'],
            $data['payload'],
            (bool) $data['needs_delivery'],
            $data['delivery_address'] ?? null,
            $data['comment'] ?? null,
            $data['full_name'] ?? null,
            $data['phone'] ?? null,
            $asClientId,
        );
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $item = $this->cancelDraft->handle($id, $this->asClientId($request));
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function promote(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'integer', 'min:1'],
            'create_client' => ['sometimes', 'boolean'],
            'billing_type' => ['required', 'string', 'in:paid,warranty'],
            'urgency' => ['nullable', 'string', 'in:normal,urgent'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'needs_delivery' => ['required', 'boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kind' => ['required', 'string', 'in:sharpening,repair'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.equipment_id' => ['nullable', 'integer', 'min:1'],
            'items.*.problem' => ['nullable', 'string'],
        ]);

        $result = $this->promoteDraft->handle(
            $id,
            isset($data['client_id']) ? (int) $data['client_id'] : null,
            (bool) ($data['create_client'] ?? false),
            $data['billing_type'],
            $data['urgency'] ?? 'normal',
            (string) $data['estimated_cost'],
            (bool) $data['needs_delivery'],
            $data['delivery_address'] ?? null,
            $data['items'],
        );

        return response()->json([
            'draft' => $result['draft']->toArray(),
            'order' => $result['order']->toArray(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function draftRules(bool $requireContacts): array
    {
        return [
            'service_type' => ['required', 'string', 'in:sharpening,repair'],
            'payload' => ['required', 'array'],
            'needs_delivery' => ['required', 'boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'full_name' => [$requireContacts ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => [$requireContacts ? 'required' : 'nullable', 'string', 'max:50'],
        ];
    }

    private function asClientId(Request $request): ?int
    {
        return (string) $request->attributes->get('actor_type') === 'clients'
            ? (int) $request->attributes->get('actor_id')
            : null;
    }
}
