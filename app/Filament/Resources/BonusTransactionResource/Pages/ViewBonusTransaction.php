<?php

namespace App\Filament\Resources\BonusTransactionResource\Pages;

use App\Filament\Resources\BonusTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBonusTransaction extends ViewRecord
{
    protected static string $resource = BonusTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
