<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Client;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Если при редактировании сменили клиента, обновим источник клиента в заказе
        if (!empty($data['client_id'])) {
            $client = Client::find($data['client_id']);

            if ($client && $client->marketing_source) {
                $data['client_source'] = $client->marketing_source;
            }
        }

        return $data;
    }
}

