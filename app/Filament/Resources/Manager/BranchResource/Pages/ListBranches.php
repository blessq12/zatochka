<?php

namespace App\Filament\Resources\Manager\BranchResource\Pages;

use App\Filament\Resources\Manager\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Создать филиал'),
        ];
    }
}
