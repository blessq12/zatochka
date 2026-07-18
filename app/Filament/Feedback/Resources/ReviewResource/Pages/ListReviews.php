<?php

namespace App\Filament\Feedback\Resources\ReviewResource\Pages;

use App\Domain\Feedback\VO\ReviewStatus;
use App\Filament\Feedback\Resources\ReviewResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected static ?string $title = 'Отзывы';

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', [
                ReviewStatus::PendingModeration->value,
                ReviewStatus::Published->value,
                ReviewStatus::Rejected->value,
            ])
            ->orderBy('submitted_at');
    }
}
