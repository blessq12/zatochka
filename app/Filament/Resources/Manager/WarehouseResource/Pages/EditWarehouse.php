<?php

namespace App\Filament\Resources\Manager\WarehouseResource\Pages;

use App\Application\UseCases\Warehouse\Warehouse\UpdateWarehouseUseCase;
use App\Application\UseCases\Warehouse\Warehouse\DeleteWarehouseUseCase;
use App\Filament\Resources\Manager\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->before(function () {
                    // Проверяем бизнес-правила перед удалением
                    try {
                        $useCase = app(DeleteWarehouseUseCase::class);
                        $useCase->loadData(['id' => $this->record->id])->validate();

                        // Если валидация прошла, выполняем Use Case (soft delete)
                        $useCase->execute();

                        Notification::make()
                            ->title('Склад успешно удален')
                            ->success()
                            ->send();

                        // Редиректим на список складов
                        $this->redirect($this->getResource()::getUrl('index'));

                        // Останавливаем стандартное удаление, так как мы уже удалили через Use Case
                        $this->halt();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка при удалении склада')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        // Останавливаем стандартное удаление
                        $this->halt();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            $data['id'] = $this->record->id;

            $useCase = app(UpdateWarehouseUseCase::class);
            $warehouse = $useCase->loadData($data)->validate()->execute();

            Notification::make()
                ->title('Склад успешно обновлен')
                ->success()
                ->send();

            // Возвращаем обновленные данные
            return [
                'branch_id' => $warehouse->getBranchId(),
                'name' => $warehouse->getName(),
                'description' => $warehouse->getDescription(),
                'is_active' => $warehouse->isActive(),
            ];
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при обновлении склада')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
