<?php

namespace App\Application\UseCases\ApiUseCases;

use App\Application\UseCases\ApiUseCases\BaseApiUseCase;
use App\Application\UseCases\ApiUseCases\DTO\OrderCreateApi;

class CreateClientOrder extends BaseApiUseCase
{
    private OrderCreateApi $orderData;

    public function validateSpecificData(): self
    {
        // Валидация DTO
        $this->orderData = OrderCreateApi::fromArray($this->data);

        return $this;
    }

    public function execute(): mixed
    {
        // 1. Поиск/создание клиента
        $client = $this->clientRepository->findByPhone($this->orderData->orderData->clientPhone);

        if (!$client) {
            // Создаем нового клиента
            $client = $this->clientRepository->create([
                'full_name' => $this->orderData->orderData->clientName,
                'phone' => $this->orderData->orderData->clientPhone,
                'is_active' => true,
                'is_deleted' => false,
            ]);
        }

        // 2. Получение главного филиала
        $mainBranch = $this->branchRepository->getMain();
        if (!$mainBranch) {
            throw new \InvalidArgumentException('Главный филиал не найден');
        }

        // 3. Генерация номера заказа
        $orderNumber = $this->orderNumberGenerator->generate();

        // 4. Подготовка данных заказа
        $orderData = [
            'client_id' => $client->getId(),
            'branch_id' => $mainBranch->getId(),
            'manager_id' => null, // Будет назначен позже
            'master_id' => null, // Будет назначен позже
            'order_number' => $orderNumber,
            'type' => $this->orderData->orderData->serviceType, // repair или sharpening
            'status' => 'new',
            'urgency' => $this->orderData->orderData->urgency->value,
            'is_paid' => false,
            'is_deleted' => false,
            'problem_description' => $this->orderData->orderData->problemDescription,
            'internal_notes' => null, // Заполняется менеджером позже
        ];

        // 5. Создание заказа
        $order = $this->orderRepository->create($orderData);

        return [
            'success' => true,
            'data' => $order->toArray(),
            'message' => 'Заказ успешно создан'
        ];
    }
}
