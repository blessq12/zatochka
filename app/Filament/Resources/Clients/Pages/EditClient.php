<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Application\ClientPortal\Command\UpdateClientProfileCommand;
use App\Application\ClientPortal\CommandHandler\UpdateClientProfileHandler;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Support\ClientFormData;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ClientFormData::prepareForForm($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var ClientModel $record */
        app(UpdateClientProfileHandler::class)->handle(new UpdateClientProfileCommand(
            clientId: (int) $record->getKey(),
            fullName: $data['full_name'],
            email: $data['email'] ?? null,
            birthDate: isset($data['birth_date']) ? (string) $data['birth_date'] : null,
            deliveryAddress: $data['delivery_address'] ?? null,
        ));

        return ClientModel::query()->findOrFail($record->getKey());
    }
}
