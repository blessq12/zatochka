<?php

namespace App\Http\Controllers\CRM\Portal;

use App\Application\CRM\Command\SubmitClientLeadCommand;
use App\Application\CRM\Command\SubmitClientLeadHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientLeadController extends Controller
{
    public function __construct(
        private SubmitClientLeadHandler $submitLead,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['nullable', 'email'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ['string'],
            'comment' => ['nullable', 'string'],
            'intake_data' => ['nullable', 'array'],
            'needs_delivery' => ['nullable', 'boolean'],
            'delivery_address' => ['nullable', 'string'],
        ]);

        $result = $this->submitLead->handle(new SubmitClientLeadCommand(
            $data['full_name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['service_types'],
            $data['comment'] ?? null,
            $data['intake_data'] ?? null,
            (bool) ($data['needs_delivery'] ?? false),
            $data['delivery_address'] ?? null,
        ));

        return response()->json([
            'data' => $result,
        ], 201);
    }
}
