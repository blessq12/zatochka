<?php

namespace App\Filament\Resources\RevenuePlanResource\Pages;

use App\Filament\Resources\RevenuePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevenuePlans extends ListRecords
{
    protected static string $resource = RevenuePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

