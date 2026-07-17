<?php

namespace App\Filament\CRM\Resources\ClientResource\Pages;

use App\Filament\CRM\Resources\ClientResource;
use App\Filament\CRM\Support\RegisterClientOption;
use App\Infrastructure\CRM\Model\ClientModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Новый клиент';

    protected function handleRecordCreation(array $data): Model
    {
        return ClientModel::query()->findOrFail(RegisterClientOption::register($data));
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Клиент зарегистрирован';
    }
}
