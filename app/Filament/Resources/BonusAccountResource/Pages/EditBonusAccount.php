<?php

namespace App\Filament\Resources\BonusAccountResource\Pages;

use App\Filament\Resources\BonusAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBonusAccount extends EditRecord
{
    protected static string $resource = BonusAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
