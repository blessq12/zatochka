<?php

namespace App\Domain\Order\DTO;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderUrgency;
use App\Domain\Order\Exception\OrderException;
use Illuminate\Support\Facades\Validator;

class UpdateOrderDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $clientId = null,
        public readonly ?OrderType $type = null,
        public readonly ?int $branchId = null,
        public readonly ?int $managerId = null,
        public readonly ?int $masterId = null,
        public readonly ?string $orderNumber = null,
        public readonly ?OrderStatus $status = null,
        public readonly ?OrderUrgency $urgency = null,
        public readonly ?bool $isPaid = null,
        public readonly ?string $paidAt = null,
        public readonly ?float $totalAmount = null,
        public readonly ?float $finalPrice = null,
        public readonly ?float $costPrice = null,
        public readonly ?float $profit = null,
        public readonly ?string $internalNotes = null,
        public readonly ?string $problemDescription = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $validator = Validator::make([
            'id' => $this->id,
            'client_id' => $this->clientId,
            'type' => $this->type?->value,
            'branch_id' => $this->branchId,
            'manager_id' => $this->managerId,
            'master_id' => $this->masterId,
            'order_number' => $this->orderNumber,
            'status' => $this->status?->value,
            'urgency' => $this->urgency?->value,
            'is_paid' => $this->isPaid,
            'paid_at' => $this->paidAt,
            'total_amount' => $this->totalAmount,
            'final_price' => $this->finalPrice,
            'cost_price' => $this->costPrice,
            'profit' => $this->profit,
            'internal_notes' => $this->internalNotes,
            'problem_description' => $this->problemDescription,
        ], [
            'id' => 'required|integer|exists:orders,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'type' => 'nullable|string|in:' . implode(',', array_column(OrderType::cases(), 'value')),
            'branch_id' => 'nullable|integer|exists:branches,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'master_id' => 'nullable|integer|exists:users,id',
            'order_number' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
            'urgency' => 'nullable|string|in:' . implode(',', array_column(OrderUrgency::cases(), 'value')),
            'is_paid' => 'nullable|boolean',
            'paid_at' => 'nullable|date',
            'total_amount' => 'nullable|numeric|min:0',
            'final_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'profit' => 'nullable|numeric',
            'internal_notes' => 'nullable|string',
            'problem_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw OrderException::validationFailed($validator->errors());
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            clientId: $data['client_id'] ?? null,
            type: isset($data['type']) ? (is_string($data['type']) ? OrderType::from($data['type']) : $data['type']) : null,
            branchId: $data['branch_id'] ?? null,
            managerId: $data['manager_id'] ?? null,
            masterId: $data['master_id'] ?? null,
            orderNumber: $data['order_number'] ?? null,
            status: isset($data['status']) ? (is_string($data['status']) ? OrderStatus::from($data['status']) : $data['status']) : null,
            urgency: isset($data['urgency']) ? (is_string($data['urgency']) ? OrderUrgency::from($data['urgency']) : $data['urgency']) : null,
            isPaid: $data['is_paid'] ?? null,
            paidAt: $data['paid_at'] ?? null,
            totalAmount: $data['total_amount'] ?? null,
            finalPrice: $data['final_price'] ?? null,
            costPrice: $data['cost_price'] ?? null,
            profit: $data['profit'] ?? null,
            internalNotes: $data['internal_notes'] ?? null,
            problemDescription: $data['problem_description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'client_id' => $this->clientId,
            'type' => $this->type?->value,
            'branch_id' => $this->branchId,
            'manager_id' => $this->managerId,
            'master_id' => $this->masterId,
            'order_number' => $this->orderNumber,
            'status' => $this->status?->value,
            'urgency' => $this->urgency?->value,
            'is_paid' => $this->isPaid,
            'paid_at' => $this->paidAt,
            'total_amount' => $this->totalAmount,
            'final_price' => $this->finalPrice,
            'cost_price' => $this->costPrice,
            'profit' => $this->profit,
            'internal_notes' => $this->internalNotes,
            'problem_description' => $this->problemDescription,
        ], fn($value) => $value !== null);
    }
}
