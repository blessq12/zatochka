<?php

namespace App\Filament\Resources\Master\RepairResource\Pages;

use App\Filament\Resources\Master\RepairResource;
use App\Application\UseCases\Repair\UpdateRepairUseCase;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRepair extends EditRecord
{
    protected static string $resource = RepairResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            // Подготовка данных для Use Case
            $useCaseData = [
                'repair_id' => $record->id,
                'master_id' => $data['master_id'] ?? null,
                'status' => $data['status'] ?? null,
                'description' => $data['description'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'work_performed' => $data['work_performed'] ?? null,
                'notes' => $data['notes'] ?? null,
                'estimated_completion' => $data['estimated_completion'] ?? null,
                'parts_used' => $data['parts_used'] ?? [],
                'additional_data' => $data['additional_data'] ?? [],
            ];

            // Выполнение Use Case
            $repair = (new UpdateRepairUseCase())
                ->loadData($useCaseData)
                ->validate()
                ->execute();

            // Обновление Eloquent модели
            $record->update([
                'master_id' => $repair->getMasterId(),
                'status' => $repair->getStatus(),
                'description' => $repair->getDescription(),
                'diagnosis' => $repair->getDiagnosis(),
                'work_performed' => $repair->getWorkPerformed(),
                'notes' => $repair->getNotes(),
                'started_at' => $repair->getStartedAt(),
                'completed_at' => $repair->getCompletedAt(),
                'estimated_completion' => $repair->getEstimatedCompletion(),
                'parts_used' => $repair->getPartsUsed(),
                'additional_data' => $repair->getAdditionalData(),
                'work_time_minutes' => $data['work_time_minutes'] ?? $record->work_time_minutes,
                'price' => $data['price'] ?? $record->price,
                'updated_at' => now(),
            ]);


            Notification::make()
                ->title('Ремонт успешно обновлен')
                ->success()
                ->send();

            return $record;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при обновлении ремонта')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
