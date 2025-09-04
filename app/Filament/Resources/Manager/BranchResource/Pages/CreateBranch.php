<?php

namespace App\Filament\Resources\Manager\BranchResource\Pages;

use App\Filament\Resources\Manager\BranchResource;
use App\Domain\Company\Services\BranchService;
use App\Domain\Company\ValueObjects\BranchCode;
use App\Domain\Company\ValueObjects\WorkingSchedule;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Преобразуем данные формы в формат, понятный доменному сервису
        return [
            'company_id' => $data['company_id'] ?? 0,
            'name' => $data['name'] ?? '',
            'code' => $data['code'] ?? '',
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

    protected function afterCreate(): void
    {
        try {
            $data = $this->data;

            // Создаем филиал через доменный сервис
            $branchService = app(BranchService::class);

            // Создаем расписание работы по умолчанию, если не указано
            $workingSchedule = null;
            if (!empty($data['working_schedule'])) {
                $workingSchedule = WorkingSchedule::fromArray($data['working_schedule']);
            }

            $branch = $branchService->createBranch(
                $data['company_id'],
                $data['name'],
                BranchCode::fromString($data['code']),
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
                ->title('Филиал создан успешно')
                ->body("Филиал '{$branch->name()}' создан с ID: {$branch->id()}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Показываем уведомление об ошибке
            Notification::make()
                ->title('Ошибка создания филиала')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
