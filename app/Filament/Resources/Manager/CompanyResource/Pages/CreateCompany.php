<?php

namespace App\Filament\Resources\Manager\CompanyResource\Pages;

use App\Filament\Resources\Manager\CompanyResource;
use App\Domain\Company\Services\CompanyService;
use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Преобразуем данные формы в формат, понятный доменному сервису
        return [
            'name' => $data['name'] ?? '',
            'legal_name' => $data['legal_name'] ?? '',
            'inn' => $data['inn'] ?? '',
            'legal_address' => $data['legal_address'] ?? '',
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

    protected function afterCreate(): void
    {
        try {
            $data = $this->data;

            // Создаем компанию через доменный сервис
            $companyService = app(CompanyService::class);

            $company = $companyService->createCompany(
                CompanyName::fromString($data['name']),
                LegalName::fromString($data['legal_name']),
                INN::fromString($data['inn']),
                $data['legal_address'],
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
                ->title('Компания создана успешно')
                ->body("Компания '{$company->name()}' создана с ID: {$company->id()}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Показываем уведомление об ошибке
            Notification::make()
                ->title('Ошибка создания компании')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
