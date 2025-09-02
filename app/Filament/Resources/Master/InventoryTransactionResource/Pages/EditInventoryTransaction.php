<?php

namespace App\Filament\Resources\Master\InventoryTransactionResource\Pages;

use App\Filament\Resources\Master\InventoryTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryTransaction extends EditRecord
{
    protected static string $resource = InventoryTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Просмотр'),
            Actions\DeleteAction::make()
                ->label('Удалить'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
