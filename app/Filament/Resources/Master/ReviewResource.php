<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\ReviewResource\Pages;
use App\Models\Review;
use App\Models\Client;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Отзывы';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о заказе')
                    ->schema([
                        Forms\Components\TextInput::make('order.order_number')
                            ->label('Номер заказа')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('client.full_name')
                            ->label('Клиент')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('order.serviceType.name')
                            ->label('Тип услуги')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Forms\Components\Section::make('Отзыв')
                    ->schema([
                        Forms\Components\TextInput::make('rating')
                            ->label('Оценка')
                            ->formatStateUsing(fn(int $state): string => str_repeat('⭐', $state))
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->rows(4)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('reply')
                            ->label('Ответ компании')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(1),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Одобрен')
                            ->disabled()
                            ->dehydrated(false),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.serviceType.name')
                    ->label('Услуга')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Оценка')
                    ->formatStateUsing(fn(int $state): string => str_repeat('⭐', $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('reply')
                    ->label('Ответ')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата отзыва')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn(Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),

                Tables\Filters\Filter::make('approved')
                    ->label('Одобренные')
                    ->query(fn(Builder $query): Builder => $query->where('is_approved', true)),

                Tables\Filters\Filter::make('pending')
                    ->label('Ожидают модерации')
                    ->query(fn(Builder $query): Builder => $query->where('is_approved', false)),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('Оценка')
                    ->options([
                        1 => '1 звезда',
                        2 => '2 звезды',
                        3 => '3 звезды',
                        4 => '4 звезды',
                        5 => '5 звезд',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Мастер не может модерировать отзывы
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('order', function (Builder $query) {
                $query->where('master_id', fn() => auth()->id());
            })
            ->with(['client', 'order.serviceType']);
    }
}
