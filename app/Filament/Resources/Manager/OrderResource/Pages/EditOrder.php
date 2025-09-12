<?php

namespace App\Filament\Resources\Manager\OrderResource\Pages;

use App\Application\UseCases\Order\UpdateOrderUseCase;
use App\Application\UseCases\Order\DeleteOrderUseCase;
use App\Domain\Order\Exception\OrderException;
use App\Filament\Resources\Manager\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(function () {
                    try {
                        (new DeleteOrderUseCase())
                            ->loadData(['id' => $this->record->id])
                            ->validate()
                            ->execute();

                        Notification::make()
                            ->title('Заказ удален')
                            ->body('Заказ #' . $this->record->order_number . ' успешно удален')
                            ->success()
                            ->send();

                        return redirect($this->getResource()::getUrl('index'));
                    } catch (OrderException $e) {
                        Notification::make()
                            ->title('Ошибка удаления')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Автоматически рассчитываем прибыль
        if (isset($data['final_price']) && isset($data['cost_price'])) {
            $data['profit'] = $data['final_price'] - $data['cost_price'];
        }

        // Устанавливаем дату оплаты если заказ помечен как оплаченный
        if (isset($data['is_paid']) && $data['is_paid'] && !isset($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $updatedOrder = (new UpdateOrderUseCase())
                ->loadData(['id' => $record->id, ...$data])
                ->validate()
                ->execute();

            Notification::make()
                ->title('Заказ обновлен')
                ->body('Заказ #' . $updatedOrder->order_number . ' успешно обновлен')
                ->success()
                ->send();

            return $updatedOrder;
        } catch (OrderException $e) {
            Notification::make()
                ->title('Ошибка обновления заказа')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
