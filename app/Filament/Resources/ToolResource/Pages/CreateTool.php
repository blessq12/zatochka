<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTool extends CreateRecord
{
    protected static ?string $title = 'Создание нового инструмента';

    protected static string $resource = ToolResource::class;
}
