<?php

namespace App\Domain\Order\DTO;

use App\Domain\Order\Exception\OrderException;
use Illuminate\Support\Facades\Validator;

class UpdateOrderDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $clientId = null,
        public readonly ?int $serviceTypeId = null,
        public readonly ?int $branchId = null,
        public readonly ?int $managerId = null,
        public readonly ?int $masterId = null,
        public readonly ?string $orderNumber = null,
        public readonly ?int $statusId = null,
        public readonly ?string $urgency = null,
        public readonly ?bool $isPaid = null,
        public readonly ?string $paidAt = null,
        public readonly ?float $totalAmount = null,
        public readonly ?float $finalPrice = null,
        public readonly ?float $costPrice = null,
        public readonly ?float $profit = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $validator = Validator::make([
            'id' => $this->id,
            'client_id' => $this->clientId,
            'service_type_id' => $this->serviceTypeId,
            'branch_id' => $this->branchId,
            'manager_id' => $this->managerId,
            'master_id' => $this->masterId,
            'order_number' => $this->orderNumber,
            'status_id' => $this->statusId,
            'urgency' => $this->urgency,
            'is_paid' => $this->isPaid,
            'paid_at' => $this->paidAt,
            'total_amount' => $this->totalAmount,
            'final_price' => $this->finalPrice,
            'cost_price' => $this->costPrice,
            'profit' => $this->profit,
        ], [
            'id' => 'required|integer|exists:orders,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'service_type_id' => 'nullable|integer|exists:service_types,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'master_id' => 'nullable|integer|exists:users,id',
            'order_number' => 'nullable|string|max:50',
            'status_id' => 'nullable|integer|exists:order_statuses,id',
            'urgency' => 'nullable|string|in:low,normal,high,urgent',
            'is_paid' => 'nullable|boolean',
            'paid_at' => 'nullable|date',
            'total_amount' => 'nullable|numeric|min:0',
            'final_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'profit' => 'nullable|numeric',
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
            serviceTypeId: $data['service_type_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            managerId: $data['manager_id'] ?? null,
            masterId: $data['master_id'] ?? null,
            orderNumber: $data['order_number'] ?? null,
            statusId: $data['status_id'] ?? null,
            urgency: $data['urgency'] ?? null,
            isPaid: $data['is_paid'] ?? null,
            paidAt: $data['paid_at'] ?? null,
            totalAmount: $data['total_amount'] ?? null,
            finalPrice: $data['final_price'] ?? null,
            costPrice: $data['cost_price'] ?? null,
            profit: $data['profit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'client_id' => $this->clientId,
            'service_type_id' => $this->serviceTypeId,
            'branch_id' => $this->branchId,
            'manager_id' => $this->managerId,
            'master_id' => $this->masterId,
            'order_number' => $this->orderNumber,
            'status_id' => $this->statusId,
            'urgency' => $this->urgency,
            'is_paid' => $this->isPaid,
            'paid_at' => $this->paidAt,
            'total_amount' => $this->totalAmount,
            'final_price' => $this->finalPrice,
            'cost_price' => $this->costPrice,
            'profit' => $this->profit,
        ], fn($value) => $value !== null);
    }
}
