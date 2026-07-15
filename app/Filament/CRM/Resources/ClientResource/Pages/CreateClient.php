<?php

namespace App\Filament\CRM\Resources\ClientResource\Pages;

use App\Application\CRM\Command\RegisterClientCommand;
use App\Application\CRM\Command\RegisterClientHandler;
use App\Filament\CRM\Resources\ClientResource;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Infrastructure\Shared\Persistence\SequentialEntityIdGenerator;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Новый клиент';

    protected function handleRecordCreation(array $data): Model
    {
        $ids = app(SequentialEntityIdGenerator::class);
        $clientId = $ids->next('client')->value;

        app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            $clientId,
            $ids->next('bonus_account')->value,
            $data['phone'],
            $data['name'],
            filled($data['email'] ?? null) ? $data['email'] : null,
        ));

        return ClientModel::query()->findOrFail($clientId);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Клиент зарегистрирован';
    }
}
