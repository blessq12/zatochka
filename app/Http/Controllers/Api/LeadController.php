<?php

namespace App\Http\Controllers\Api;

use App\Application\ClientPortal\Command\SubmitSiteLeadCommand;
use App\Application\ClientPortal\CommandHandler\SubmitSiteLeadHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class LeadController
{
    public function store(Request $request, SubmitSiteLeadHandler $handler): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'service_types' => ['required', 'array', 'min:1'],
            'service_types.*' => ['string', Rule::in(['sharpening', 'repair'])],
            'comment' => ['nullable', 'string', 'max:2000'],
            'intake_data' => ['required', 'array'],
            'intake_data.tool_type' => [
                Rule::requiredIf(fn (): bool => $this->hasServiceType($request, 'sharpening')),
                'nullable',
                'string',
                'max:50',
            ],
            'intake_data.tools_count' => [
                Rule::requiredIf(fn (): bool => $this->hasServiceType($request, 'sharpening')),
                'nullable',
                'integer',
                'min:1',
            ],
            'intake_data.extra_comment' => ['nullable', 'string', 'max:2000'],
            'intake_data.equipment_type' => [
                Rule::requiredIf(fn (): bool => $this->hasServiceType($request, 'repair')),
                'nullable',
                'string',
                Rule::in(['clipper', 'trimmer', 'shaver', 'dryer', 'other']),
            ],
            'intake_data.device_name' => [
                Rule::requiredIf(fn (): bool => $this->hasServiceType($request, 'repair')),
                'nullable',
                'string',
                'max:255',
            ],
            'intake_data.problem_description' => [
                Rule::requiredIf(fn (): bool => $this->hasServiceType($request, 'repair')),
                'nullable',
                'string',
                'max:2000',
            ],
            'intake_data.urgency_type' => ['nullable', 'string', Rule::in(['standard', 'urgent'])],
            'needs_delivery' => ['boolean'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $lead = $handler->handle(new SubmitSiteLeadCommand(
            fullName: $validated['full_name'],
            phone: $validated['phone'],
            serviceTypes: $validated['service_types'],
            email: $validated['email'] ?? null,
            comment: $validated['comment'] ?? null,
            intakeData: $validated['intake_data'],
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

    private function hasServiceType(Request $request, string $type): bool
    {
        return in_array($type, $request->input('service_types', []), true);
    }
}
