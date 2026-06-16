<?php

namespace App\Http\Controllers\Api;

use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Application\ClientPortal\CommandHandler\SubmitSiteLeadHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LeadController
{
    public function store(Request $request, SubmitSiteLeadHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ['string', 'in:sharpening,repair'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'needs_delivery' => ['boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $lead = $handler->handle(new SubmitSiteLeadCommand(
            fullName: $validated['full_name'],
            phone: $validated['phone'],
            serviceTypes: $validated['service_types'],
            email: $validated['email'] ?? null,
            comment: $validated['comment'] ?? null,
            needsDelivery: (bool) ($validated['needs_delivery'] ?? false),
            deliveryAddress: $validated['delivery_address'] ?? null,
        ));

        return response()->json([
            'data' => [
                'id' => $lead->id(),
                'message' => 'Заявка принята. Менеджер свяжется с вами.',
            ],
        ], 201);
    }
}
