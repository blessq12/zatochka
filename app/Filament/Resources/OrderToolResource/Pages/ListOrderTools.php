<?php

namespace App\Filament\Resources\OrderToolResource\Pages;

use App\Filament\Resources\OrderToolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderTools extends ListRecords
{
    protected static string $resource = OrderToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
