<?php

namespace App\Filament\Resources\Manager\CompanyResource\Pages;

use App\Filament\Resources\Manager\CompanyResource;
use App\Domain\Company\Services\CompanyService;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

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
            'name' => $data['name'] ?? '',
            'legal_name' => $data['legal_name'] ?? '',
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_bik' => $data['bank_bik'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'bank_cor_account' => $data['bank_cor_account'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'additional_data' => $data['additional_data'] ?? [],
        ];
    }

    protected function afterSave(): void
    {
        try {
            $data = $this->data;
            $companyId = $this->record->id;

            // Обновляем компанию через доменный сервис
            $companyService = app(CompanyService::class);

            $company = $companyService->updateCompany(
                $companyId,
                CompanyName::fromString($data['name']),
                LegalName::fromString($data['legal_name']),
                $data['description'],
                $data['website'],
                $data['phone'],
                $data['email'],
                $data['bank_name'],
                $data['bank_bik'],
                $data['bank_account'],
                $data['bank_cor_account'],
                $data['logo_path'],
                $data['additional_data'] ?? []
            );

            // Показываем уведомление об успехе
            Notification::make()
                ->title('Компания обновлена успешно')
                ->body("Компания '{$company->name()}' обновлена")
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Показываем уведомление об ошибке
            Notification::make()
                ->title('Ошибка обновления компании')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
