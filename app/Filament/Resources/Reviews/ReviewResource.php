<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ReviewModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = ReviewModel::class;

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $modelLabel = 'отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
