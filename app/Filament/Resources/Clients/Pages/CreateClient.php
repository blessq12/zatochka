<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Application\ClientPortal\Command\CreateClientCommand;
use App\Application\ClientPortal\CommandHandler\CreateClientHandler;
use App\Domain\ClientPortal\Exception\ClientAlreadyRegisteredException;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema, isCreate: true);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $client = app(CreateClientHandler::class)->handle(new CreateClientCommand(
                phone: $data['phone'],
                fullName: $data['full_name'],
                email: $data['email'] ?? null,
                birthDate: isset($data['birth_date']) ? (string) $data['birth_date'] : null,
                deliveryAddress: $data['delivery_address'] ?? null,
            ));
        } catch (ClientAlreadyRegisteredException $exception) {
            throw ValidationException::withMessages([
                'phone' => $exception->getMessage(),
            ]);
        }

        $clientId = $client->id();

        if ($clientId === null) {
            throw new \RuntimeException('Не удалось создать клиента.');
        }

        return ClientModel::query()->findOrFail($clientId);
    }

    protected function getRedirectUrl(): string
    {
        return ClientResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
