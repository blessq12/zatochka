<?php

namespace App\Filament\Resources\Master\OrderResource\Pages;

use App\Filament\Resources\Master\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_repair')
                ->label('Создать ремонт')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('success')
                ->url(fn(): string => route('filament.master.resources.master.repairs.create', ['order_id' => $this->record->id]))
                ->visible(fn(): bool => !$this->record->repair),
        ];
    }
}
