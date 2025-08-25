<?php

namespace App\DTO;

class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $client_id,
        public string $service_type,
        public string $tool_type,
        public float $total_amount,
        public string $status = 'new',
        public ?string $problem_description = null,
        public ?string $work_description = null,
        public ?array $tools_photos = null,
        public bool $needs_consultation = false,
        public int $total_tools_count = 1,
        public bool $needs_delivery = false,
        public ?string $delivery_address = null,
        public ?string $equipment_name = null,
        public string $urgency = 'normal',
        public ?float $discount_percent = null,
        public ?float $discount_amount = null,
        public ?float $final_price = null,
        public ?string $used_materials = null,
        public ?float $cost_price = null,
        public ?float $profit = null,
    ) {
        // Валидация обязательных полей
        if (empty($this->client_id)) {
            throw new \InvalidArgumentException('Client ID is required');
        }

        if (empty($this->service_type)) {
            throw new \InvalidArgumentException('Service type is required');
        }

        if (empty($this->tool_type)) {
            throw new \InvalidArgumentException('Tool type is required');
        }

        if ($this->total_amount <= 0) {
            throw new \InvalidArgumentException('Total amount must be greater than 0');
        }

        // Валидация типов услуг
        $validServiceTypes = ['sharpening', 'repair'];
        if (!in_array($this->service_type, $validServiceTypes)) {
            throw new \InvalidArgumentException('Invalid service type');
        }

        // Валидация типов инструментов
        $validToolTypes = ['manicure', 'hair', 'grooming', 'clipper', 'dryer', 'kitchen', 'garden', 'other'];
        if (!in_array($this->tool_type, $validToolTypes)) {
            throw new \InvalidArgumentException('Invalid tool type');
        }

        // Валидация срочности
        $validUrgencyLevels = ['normal', 'urgent', 'express'];
        if (!in_array($this->urgency, $validUrgencyLevels)) {
            throw new \InvalidArgumentException('Invalid urgency level');
        }

        // Валидация доставки
        if ($this->needs_delivery && empty($this->delivery_address)) {
            throw new \InvalidArgumentException('Delivery address is required when delivery is needed');
        }

        // Автоматический расчет финальной цены если не указана
        if ($this->final_price === null) {
            $this->final_price = $this->total_amount;
            if ($this->discount_amount) {
                $this->final_price -= $this->discount_amount;
            } elseif ($this->discount_percent) {
                $this->final_price = $this->total_amount * (1 - $this->discount_percent / 100);
            }
        }

        // Автоматический расчет прибыли если указана себестоимость
        if ($this->profit === null && $this->cost_price !== null) {
            $this->profit = $this->final_price - $this->cost_price;
        }
    }

    /**
     * Создать DTO из запроса
     */
    public static function fromRequest(array $data): static
    {
        // Проверяем обязательные поля
        if (!isset($data['client_id'])) {
            throw new \InvalidArgumentException('Client ID is required');
        }
        if (!isset($data['service_type'])) {
            throw new \InvalidArgumentException('Service type is required');
        }
        if (!isset($data['tool_type'])) {
            throw new \InvalidArgumentException('Tool type is required');
        }
        if (!isset($data['total_amount'])) {
            throw new \InvalidArgumentException('Total amount is required');
        }

        return new static(
            client_id: $data['client_id'],
            service_type: $data['service_type'],
            tool_type: $data['tool_type'],
            total_amount: $data['total_amount'],
            status: $data['status'] ?? 'new',
            problem_description: $data['problem_description'] ?? null,
            work_description: $data['work_description'] ?? null,
            tools_photos: $data['tools_photos'] ?? null,
            needs_consultation: $data['needs_consultation'] ?? false,
            total_tools_count: $data['total_tools_count'] ?? 1,
            needs_delivery: $data['needs_delivery'] ?? false,
            delivery_address: $data['delivery_address'] ?? null,
            equipment_name: $data['equipment_name'] ?? null,
            urgency: $data['urgency'] ?? 'normal',
            discount_percent: $data['discount_percent'] ?? null,
            discount_amount: $data['discount_amount'] ?? null,
            final_price: $data['final_price'] ?? null,
            used_materials: $data['used_materials'] ?? null,
            cost_price: $data['cost_price'] ?? null,
            profit: $data['profit'] ?? null,
        );
    }

    /**
     * Получить данные для создания заказа
     */
    public function getOrderData(): array
    {
        return [
            'client_id' => $this->client_id,
            'service_type' => $this->service_type,
            'tool_type' => $this->tool_type,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'problem_description' => $this->problem_description,
            'work_description' => $this->work_description,
            'tools_photos' => $this->tools_photos,
            'needs_consultation' => $this->needs_consultation,
            'total_tools_count' => $this->total_tools_count,
            'needs_delivery' => $this->needs_delivery,
            'delivery_address' => $this->delivery_address,
            'equipment_name' => $this->equipment_name,
            'urgency' => $this->urgency,
            'discount_percent' => $this->discount_percent,
            'discount_amount' => $this->discount_amount,
            'final_price' => $this->final_price,
            'used_materials' => $this->used_materials,
            'cost_price' => $this->cost_price,
            'profit' => $this->profit,
        ];
    }
}
