<?php

namespace App\Filament\Resources\Manager\ReviewResource\Pages;

use App\Filament\Resources\Manager\ReviewResource;

use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Убираем создание отзывов - они создаются клиентами
        ];
    }
}
