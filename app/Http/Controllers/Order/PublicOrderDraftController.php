<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\CreatePublicOrderDraftHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicOrderDraftController extends Controller
{
    public function __construct(
        private CreatePublicOrderDraftHandler $createPublicDraft,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'in:sharpening,repair'],
            'comment' => ['nullable', 'string'],
            'intake_data' => ['nullable', 'array'],
            'needs_delivery' => ['required', 'boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = [
            'intake_data' => $data['intake_data'] ?? [],
        ];

        $item = $this->createPublicDraft->handle(
            $data['full_name'],
            $data['phone'],
            $data['service_type'],
            $payload,
            (bool) $data['needs_delivery'],
            $data['delivery_address'] ?? null,
            $data['comment'] ?? null,
        );

        return response()->json($item->toArray(), 201);
    }
}
