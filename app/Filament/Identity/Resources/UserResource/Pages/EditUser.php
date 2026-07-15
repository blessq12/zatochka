<?php

namespace App\Filament\Identity\Resources\UserResource\Pages;

use App\Filament\Identity\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Редактирование сотрудника';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Удалить'),
        ];
    }
}
