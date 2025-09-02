<?php

namespace App\Filament\Resources\Master\ReviewResource\Pages;

use App\Filament\Resources\Master\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Мастер может только просматривать отзывы
        ];
    }
}
