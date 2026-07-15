<?php

namespace App\Filament\Identity\Resources\UserResource\Pages;

use App\Filament\Identity\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Новый сотрудник';
}
