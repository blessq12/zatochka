<?php

namespace App\Filament\Resources\Master\ReviewResource\Pages;

use App\Filament\Resources\Master\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Мастер не может создавать отзывы
        ];
    }
}
