<?php

namespace App\Filament\CRM\Resources\ClientResource\Pages;

use App\Application\CRM\Command\UpdateClientCommand;
use App\Application\CRM\Command\UpdateClientHandler;
use App\Filament\CRM\Resources\ClientResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Редактирование клиента';

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(UpdateClientHandler::class)->handle(new UpdateClientCommand(
            (int) $record->getKey(),
            $data['name'],
            $data['phone'],
            filled($data['email'] ?? null) ? $data['email'] : null,
        ));

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Клиент обновлён';
    }
}
