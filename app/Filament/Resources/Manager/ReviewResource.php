<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\ReviewResource\Pages;
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

    protected static ?string $navigationGroup = 'Модерация';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->options(Client::active()->pluck('full_name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->options(Order::active()->pluck('order_number', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('rating')
                            ->label('Оценка')
                            ->options([
                                1 => '1 звезда',
                                2 => '2 звезды',
                                3 => '3 звезды',
                                4 => '4 звезды',
                                5 => '5 звезд',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->rows(4)
                            ->required()
                            ->maxLength(1000),
                    ])->columns(2),

                Forms\Components\Section::make('Модерация')
                    ->schema([
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Одобрен')
                            ->default(false),

                        Forms\Components\Textarea::make('reply')
                            ->label('Ответ компании')
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Дополнительные данные')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значение'),
                    ])->columns(1),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])->collapsible(),
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
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('reply')
                    ->label('Ответ')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата отзыва')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn (Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),

                Tables\Filters\Filter::make('pending')
                    ->label('Ожидают модерации')
                    ->query(fn (Builder $query): Builder => $query->where('is_approved', false))
                    ->default(),

                Tables\Filters\Filter::make('approved')
                    ->label('Одобренные')
                    ->query(fn (Builder $query): Builder => $query->where('is_approved', true)),

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => true]);
                    })
                    ->visible(fn (Review $record): bool => !$record->is_approved),

                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => false]);
                    })
                    ->visible(fn (Review $record): bool => $record->is_approved),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Одобрить выбранные')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each(function ($record) {
                                $record->update(['is_approved' => true]);
                            });
                        }),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Отклонить выбранные')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records): void {
                            $records->each(function ($record) {
                                $record->update(['is_approved' => false]);
                            });
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'order']);
    }
}
