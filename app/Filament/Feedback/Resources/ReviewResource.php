<?php

namespace App\Filament\Feedback\Resources;

use App\Filament\Feedback\Resources\ReviewResource\Pages\ListReviews;
use App\Filament\Feedback\Resources\ReviewResource\Pages\ViewReview;
use App\Filament\Feedback\Resources\ReviewResource\Support\ReviewInfolist;
use App\Filament\Feedback\Resources\ReviewResource\Support\ReviewPresentation;
use App\Filament\Support\DomainResource;
use App\Infrastructure\Feedback\Model\ReviewModel;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ReviewResource extends DomainResource
{
    protected static ?string $model = ReviewModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Клиенты';

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'order.items.equipment.components']);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(ReviewInfolist::components());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->label('Заказ')
                    ->formatStateUsing(fn (?string $state, ReviewModel $record): string => ReviewPresentation::orderNumberLabel($record))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('order', function (Builder $order) use ($search): void {
                            $order->where('number', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('client_id')
                    ->label('Клиент')
                    ->formatStateUsing(fn (?int $state, ReviewModel $record): string => ReviewPresentation::clientName($record))
                    ->description(fn (ReviewModel $record): string => ReviewPresentation::clientPhone($record))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('client', function (Builder $client) use ($search): void {
                            $client->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Оценка')
                    ->html()
                    ->formatStateUsing(fn (?int $state): Htmlable => ReviewPresentation::ratingStarsHtml((int) ($state ?? 0)))
                    ->sortable(),
                TextColumn::make('listing_flags')
                    ->label('Статус')
                    ->state(fn (ReviewModel $record): Htmlable => ReviewPresentation::listingFlagsHtml($record))
                    ->html()
                    ->alignCenter(),
                TextColumn::make('submitted_at')
                    ->label('Отправлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ReviewPresentation::listingStatusFilterOptions()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр')
                    ->icon(Heroicon::OutlinedEye)
                    ->iconButton()
                    ->tooltip('Просмотр'),
            ], RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'view' => ViewReview::route('/{record}'),
        ];
    }
}
