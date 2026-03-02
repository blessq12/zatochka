<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?string $navigationGroup = 'Клиенты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Отзыв')
                    ->schema([
                        Forms\Components\Placeholder::make('client.full_name')
                            ->label('Клиент')
                            ->content(fn (?Review $record) => $record?->client?->full_name ?? '—'),
                        Forms\Components\Placeholder::make('order.order_number')
                            ->label('Заказ')
                            ->content(fn (?Review $record) => $record?->order?->order_number ?? '—'),
                        Forms\Components\Placeholder::make('rating')
                            ->label('Оценка')
                            ->content(fn (?Review $record) => $record ? "{$record->rating} / 5" : '—'),
                        Forms\Components\Placeholder::make('comment')
                            ->label('Текст отзыва')
                            ->content(fn (?Review $record) => $record?->comment ?? '—')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Одобрен'),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Показывать на сайте'),
                        Forms\Components\Textarea::make('reply')
                            ->label('Ответ на отзыв')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Отзыв')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Одобрен')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('Виден')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Одобрен')
                    ->placeholder('Все')
                    ->trueLabel('Одобренные')
                    ->falseLabel('Не одобренные'),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label('Виден на сайте')
                    ->placeholder('Все')
                    ->trueLabel('Видимые')
                    ->falseLabel('Скрытые'),
                Tables\Filters\Filter::make('is_deleted')
                    ->query(fn (Builder $q) => $q->where('is_deleted', false))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Редактировать'),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
