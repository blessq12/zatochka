<?php

namespace App\Application\UseCases\Repair;

use App\Domain\Repair\Entity\Repair;
use App\Domain\Repair\Enum\RepairStatus;
use App\Domain\Order\Entity\Order;
use App\Domain\Company\Entity\User;

class CreateRepairUseCase extends BaseRepairUseCase
{
    protected function validateSpecificData(): self
    {
        // Валидация заказа
        if (empty($this->data['order_id'])) {
            throw new \InvalidArgumentException('Order ID is required');
        }

        $order = $this->orderRepository->get($this->data['order_id']);
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        // Проверяем, что для заказа еще не создан ремонт
        $existingRepairs = $this->repairRepository->getByOrderId($this->data['order_id']);
        if (!empty($existingRepairs)) {
            throw new \InvalidArgumentException('Repair already exists for this order');
        }

        // Валидация мастера (если указан)
        if (!empty($this->data['master_id'])) {
            $master = $this->userRepository->get($this->data['master_id']);
            if (!$master) {
                throw new \InvalidArgumentException('Master not found');
            }
        }

        // Валидация статуса
        if (!empty($this->data['status'])) {
            $validStatuses = RepairStatus::getAll();
            if (!in_array($this->data['status'], $validStatuses)) {
                throw new \InvalidArgumentException('Invalid repair status');
            }
        }

        return $this;
    }

    public function execute(): Repair
    {
        $order = $this->orderRepository->get($this->data['order_id']);
        $repairNumber = $this->generateRepairNumber($order);
        $repairData = [
            'number' => $repairNumber,
            'order_id' => $this->data['order_id'],
            'master_id' => $this->data['master_id'] ?? null,
            'status' => $this->data['status'] ?? RepairStatus::PENDING->value,
            'description' => $this->data['description'] ?? null,
            'diagnosis' => $this->data['diagnosis'] ?? null,
            'work_performed' => $this->data['work_performed'] ?? null,
            'notes' => $this->data['notes'] ?? null,
            'started_at' => $this->data['started_at'] ?? null,
            'estimated_completion' => $this->data['estimated_completion'] ?? null,
            'parts_used' => $this->data['parts_used'] ?? [],
            'additional_data' => $this->data['additional_data'] ?? [],
            'work_time_minutes' => $this->data['work_time_minutes'] ?? 0,
            'price' => $this->data['price'] ?? 0,
        ];

        return $this->repairRepository->create($repairData);
    }

    private function generateRepairNumber(Order $order): string
    {
        // Формат: R-{ORDER_NUMBER}-{SEQUENCE}
        $orderNumber = $order->getOrderNumber();
        $sequence = $this->getNextSequenceForOrder($order->getId());

        return "R-{$orderNumber}-{$sequence}";
    }

    private function getNextSequenceForOrder(int $orderId): int
    {
        $existingRepairs = $this->repairRepository->getByOrderId($orderId);
        return count($existingRepairs) + 1;
    }
}
