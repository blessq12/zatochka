<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    protected static ?string $navigationGroup = 'Основные';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация об отзыве')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('rating')
                            ->label('Рейтинг')
                            ->options([
                                1 => '1 звезда',
                                2 => '2 звезды',
                                3 => '3 звезды',
                                4 => '4 звезды',
                                5 => '5 звезд',
                            ])
                            ->required()
                            ->default(5),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Содержание отзыва')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('reply')
                            ->label('Ответ менеджера')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Одобрен')
                            ->helperText('Одобренные отзывы отображаются публично'),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])
                    ->collapsible(),
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
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Рейтинг')
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state)." ({$state}/5)")
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('reply')
                    ->label('Ответ')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return $state && strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Одобрен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата отзыва')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Удален')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Рейтинг')
                    ->options([
                        1 => '1 звезда',
                        2 => '2 звезды',
                        3 => '3 звезды',
                        4 => '4 звезды',
                        5 => '5 звезд',
                    ]),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Статус одобрения')
                    ->placeholder('Все отзывы')
                    ->trueLabel('Только одобренные')
                    ->falseLabel('Только неодобренные'),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все отзывы')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),

                Tables\Filters\Filter::make('has_reply')
                    ->label('С ответом')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('reply')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record): bool => ! $record->is_approved)
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('Отзыв одобрен')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('disapprove')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (Review $record): bool => $record->is_approved)
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => false]);
                        \Filament\Notifications\Notification::make()
                            ->title('Отзыв отклонен')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Одобрить выбранные')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records): void {
                            $records->each->update(['is_approved' => true]);
                            \Filament\Notifications\Notification::make()
                                ->title('Выбранные отзывы одобрены')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('disapprove_selected')
                        ->label('Отклонить выбранные')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records): void {
                            $records->each->update(['is_approved' => false]);
                            \Filament\Notifications\Notification::make()
                                ->title('Выбранные отзывы отклонены')
                                ->warning()
                                ->send();
                        }),
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
}
