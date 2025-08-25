<?php

namespace App\DTOs;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public readonly string $serviceType,
        public readonly string $clientName,
        public readonly string $clientPhone,
        public readonly bool $agreement,
        public readonly bool $privacyAgreement,
        public readonly ?string $toolType = null,
        public readonly ?int $totalToolsCount = null,
        public readonly bool $needsDelivery = false,
        public readonly ?string $deliveryAddress = null,
        public readonly ?string $equipmentName = null,
        public readonly ?string $equipmentType = null,
        public readonly ?string $problemDescription = null,
        public readonly string $urgency = 'normal'
    ) {
        if (!$this->validate()) {
            throw new ValidationException(Validator::make([], []));
        }
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'service_type' => $this->serviceType,
            'client_name' => $this->clientName,
            'client_phone' => $this->clientPhone,
            'agreement' => $this->agreement,
            'privacy_agreement' => $this->privacyAgreement,
            'tool_type' => $this->toolType,
            'total_tools_count' => $this->totalToolsCount,
            'needs_delivery' => $this->needsDelivery,
            'delivery_address' => $this->deliveryAddress,
            'equipment_name' => $this->equipmentName,
            'equipment_type' => $this->equipmentType,
            'problem_description' => $this->problemDescription,
            'urgency' => $this->urgency,
        ];
    }

    /**
     * Validate DTO data
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->toArray(), static::rules());
        return !$validator->fails();
    }

    /**
     * Get validation rules
     */
    public static function rules(): array
    {
        return [
            'service_type' => 'required|in:sharpening,repair',
            'client_name' => 'required|string|min:2|max:255',
            'client_phone' => 'required|string|min:10|max:20',
            'agreement' => 'required|boolean',
            'privacy_agreement' => 'required|boolean',
            'tool_type' => 'required_if:service_type,sharpening|string|max:255',
            'total_tools_count' => 'required_if:service_type,sharpening|integer|min:1',
            'needs_delivery' => 'boolean',
            'delivery_address' => 'required_if:needs_delivery,true|string|min:10|max:500',
            'equipment_name' => 'required_if:service_type,repair|string|max:255',
            'equipment_type' => 'required_if:service_type,repair|string|max:255',
            'problem_description' => 'required_if:service_type,repair|string|min:10|max:1000',
            'urgency' => 'in:normal,urgent',
        ];
    }

    /**
     * Create from request data
     */
    public static function fromRequest(array $data): static
    {
        return new static(
            serviceType: $data['service_type'],
            clientName: $data['client_name'],
            clientPhone: $data['client_phone'],
            agreement: $data['agreement'],
            privacyAgreement: $data['privacy_agreement'],
            toolType: $data['tool_type'] ?? null,
            totalToolsCount: $data['total_tools_count'] ?? null,
            needsDelivery: $data['needs_delivery'] ?? false,
            deliveryAddress: $data['delivery_address'] ?? null,
            equipmentName: $data['equipment_name'] ?? null,
            equipmentType: $data['equipment_type'] ?? null,
            problemDescription: $data['problem_description'] ?? null,
            urgency: $data['urgency'] ?? 'normal'
        );
    }
}
