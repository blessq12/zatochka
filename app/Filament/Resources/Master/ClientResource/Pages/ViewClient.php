<?php

namespace App\Filament\Resources\Master\ClientResource\Pages;

use App\Filament\Resources\Master\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_orders')
                ->label('Заказы клиента')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(fn(): string => route('filament.master.resources.master.orders.index', ['tableFilters[client_id][value]' => $this->record->id])),
        ];
    }
}
