<?php

namespace App\Filament\Resources\RepairResource\Pages;

use App\Filament\Resources\RepairResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRepair extends CreateRecord
{
    protected static string $resource = RepairResource::class;
    protected static ?string $title = 'Создание нового ремонта';
}
