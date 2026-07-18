<?php

namespace App\Filament\SiteContent\Resources\SiteContactsResource\Pages;

use App\Application\SiteContent\Command\UpdateSiteContactsCommand;
use App\Application\SiteContent\Command\UpdateSiteContactsHandler;
use App\Filament\SiteContent\Resources\SiteContactsResource;
use App\Shared\Domain\DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class EditSiteContacts extends EditRecord
{
    protected static string $resource = SiteContactsResource::class;

    protected static ?string $title = 'Контакты сайта';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $details = $data['address_details'] ?? [];
        $data['address_details'] = array_map(
            static fn ($detail): array => is_array($detail) ? $detail : ['detail' => (string) $detail],
            array_values((array) $details),
        );

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $details = [];

        foreach ((array) ($data['address_details'] ?? []) as $row) {
            $details[] = is_array($row) ? (string) ($row['detail'] ?? '') : (string) $row;
        }

        try {
            app(UpdateSiteContactsHandler::class)->handle(new UpdateSiteContactsCommand(
                (string) ($data['contact_person'] ?? ''),
                (string) ($data['phone'] ?? ''),
                (string) ($data['phone_tel'] ?? ''),
                (string) ($data['email'] ?? ''),
                (string) ($data['address_main'] ?? ''),
                $details,
                array_values((array) ($data['social_links'] ?? [])),
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
        return 'Контакты обновлены';
    }
}
