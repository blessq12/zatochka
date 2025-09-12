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
        // Устанавливаем текущего пользователя как менеджера если не указан
        if (empty($data['manager_id'])) {
            $data['manager_id'] = \Illuminate\Support\Facades\Auth::id();
        }

        if (empty($data['branch_id'])) {
            $mainBranch = \App\Models\Branch::where('is_main', true)->first();
            if ($mainBranch) {
                $data['branch_id'] = $mainBranch->id;
            } else {
                // Если главный филиал не найден, берем первый доступный
                $firstBranch = \App\Models\Branch::first();
                if ($firstBranch) {
                    $data['branch_id'] = $firstBranch->id;
                }
            }
        }

        // Статус и тип теперь устанавливаются по умолчанию в форме

        // Номер заказа теперь генерируется в UseCase через OrderNumberGeneratorService

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $orderEntity = (new CreateOrderUseCase())
                ->loadData($data)
                ->validate()
                ->execute();

            // Получаем Eloquent модель для Filament
            $eloquentOrder = \App\Models\Order::findOrFail($orderEntity->getId());

            Notification::make()
                ->title('Заказ создан')
                ->body('Заказ #' . $orderEntity->getOrderNumber() . ' успешно создан')
                ->success()
                ->send();

            return $eloquentOrder;
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
