<?php

namespace App\Filament\SiteContent\Resources\CompanyProfileResource\Pages;

use App\Application\SiteContent\Command\UpdateCompanyProfileCommand;
use App\Application\SiteContent\Command\UpdateCompanyProfileHandler;
use App\Filament\SiteContent\Resources\CompanyProfileResource;
use App\Shared\Domain\DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class EditCompanyProfile extends EditRecord
{
    protected static string $resource = CompanyProfileResource::class;

    protected static ?string $title = 'Реквизиты компании';

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(UpdateCompanyProfileHandler::class)->handle(new UpdateCompanyProfileCommand(
                (string) ($data['owner_name'] ?? ''),
                (string) ($data['inn'] ?? ''),
                (string) ($data['ogrn'] ?? ''),
                (string) ($data['legal_address'] ?? ''),
                (string) ($data['actual_address'] ?? ''),
            ));
        } catch (DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
            $this->halt();
        } catch (Throwable $e) {
            Notification::make()->danger()->title('Не удалось сохранить')->send();
            $this->halt();
        }

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Компания обновлена';
    }
}
