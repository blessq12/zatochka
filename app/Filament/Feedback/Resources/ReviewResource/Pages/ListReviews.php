<?php

namespace App\Filament\Feedback\Resources\ReviewResource\Pages;

use App\Filament\Feedback\Resources\ReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected static ?string $title = 'Отзывы на модерации';
}
