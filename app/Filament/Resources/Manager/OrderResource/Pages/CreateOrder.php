<?php

namespace App\Filament\Resources\Manager\OrderResource\Pages;

use App\Application\UseCases\Order\CreateOrderUseCase;
use App\Domain\Order\Exception\OrderException;
use App\Filament\Resources\Manager\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Генерируем номер заказа если не указан
        if (empty($data['order_number'])) {
            $data['order_number'] = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        // Устанавливаем текущего пользователя как менеджера если не указан
        if (empty($data['manager_id'])) {
            $data['manager_id'] = auth()->user()->id;
        }

        if (empty($data['status_id'])) {
            $data['status_id'] = 1; // ID статуса "Новый"
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $order = (new CreateOrderUseCase())
                ->loadData($data)
                ->validate()
                ->execute();

            Notification::make()
                ->title('Заказ создан')
                ->body('Заказ #' . $order->order_number . ' успешно создан')
                ->success()
                ->send();

            return $order;
        } catch (OrderException $e) {
            Notification::make()
                ->title('Ошибка создания заказа')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
