<?php

namespace App\Filament\Resources\Manager\BranchResource\Pages;

use App\Filament\Resources\Manager\BranchResource;
use App\Domain\Company\Services\BranchService;
use App\Domain\Company\ValueObjects\WorkingSchedule;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Преобразуем данные формы в формат, понятный доменному сервису
        return [
            'company_id' => $data['company_id'] ?? $this->record->company_id,
            'code' => $data['code'] ?? $this->record->code,
            'name' => $data['name'] ?? '',
            'address' => $data['address'] ?? '',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'working_schedule' => $data['working_schedule'] ?? null,
            'opening_time' => $data['opening_time'] ?? null,
            'closing_time' => $data['closing_time'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'description' => $data['description'] ?? null,
            'additional_data' => $data['additional_data'] ?? [],
        ];
    }

    protected function afterSave(): void
    {
        try {
            $data = $this->data;
            $branchId = $this->record->id;

            // Обновляем филиал через доменный сервис
            $branchService = app(BranchService::class);

            // Создаем расписание работы, если указано
            $workingSchedule = null;
            if (!empty($data['working_schedule'])) {
                $workingSchedule = WorkingSchedule::fromArray($data['working_schedule']);
            }

            $branch = $branchService->updateBranch(
                $branchId,
                $data['name'],
                $data['address'],
                $data['phone'],
                $data['email'],
                $workingSchedule,
                $data['opening_time'],
                $data['closing_time'],
                $data['latitude'],
                $data['longitude'],
                $data['description'],
                $data['additional_data'] ?? []
            );

            // Показываем уведомление об успехе
            Notification::make()
                ->title('Филиал обновлен успешно')
                ->body("Филиал '{$branch->name()}' обновлен")
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Показываем уведомление об ошибке
            Notification::make()
                ->title('Ошибка обновления филиала')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
