<?php

namespace App\Filament\Resources\Master\RepairResource\Pages;

use App\Filament\Resources\Master\RepairResource;
use App\Application\UseCases\Repair\CreateRepairUseCase;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRepair extends CreateRecord
{
    protected static string $resource = RepairResource::class;


    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            // Подготовка данных для Use Case
            $useCaseData = [
                'order_id' => $data['order_id'],
                'master_id' => $data['master_id'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'description' => $data['description'],
                'diagnosis' => $data['diagnosis'] ?? null,
                'work_performed' => $data['work_performed'] ?? null,
                'notes' => $data['notes'] ?? null,
                'started_at' => $data['started_at'] ?? null,
                'estimated_completion' => $data['estimated_completion'] ?? null,
                'parts_used' => $data['parts_used'] ?? [],
                'additional_data' => $data['additional_data'] ?? [],
            ];

            // Выполнение Use Case
            $repair = (new CreateRepairUseCase())
                ->loadData($useCaseData)
                ->validate()
                ->execute();

            // Создание Eloquent модели для Filament
            $repairModel = new \App\Models\Repair();
            $repairModel->fill([
                'id' => $repair->getId(),
                'number' => $repair->getNumber(),
                'order_id' => $repair->getOrderId(),
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
                'work_time_minutes' => $data['work_time_minutes'] ?? null,
                'price' => $data['price'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $repairModel->save();


            Notification::make()
                ->title('Ремонт успешно создан')
                ->success()
                ->send();

            return $repairModel;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при создании ремонта')
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
