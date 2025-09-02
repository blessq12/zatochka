<?php

namespace App\Filament\Resources\Manager\ReviewResource\Pages;

use App\Filament\Resources\Manager\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Редактировать'),
        ];
    }
}
