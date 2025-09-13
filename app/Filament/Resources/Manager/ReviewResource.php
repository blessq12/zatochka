<?php

namespace App\Filament\Resources\Manager;

use App\Application\UseCases\Review\UpdateReviewUseCase;
use App\Application\UseCases\Review\DeleteReviewUseCase;
use App\Filament\Resources\Manager\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ReviewResource extends Resource
{
    protected static ?string $model = \App\Models\Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Заказы';
    protected static ?string $pluralLabel = 'Отзывы';
    protected static ?string $label = 'Отзыв';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Select::make('rating')
                            ->label('Оценка')
                            ->options([
                                1 => '1 звезда - Очень плохо',
                                2 => '2 звезды - Плохо',
                                3 => '3 звезды - Удовлетворительно',
                                4 => '4 звезды - Хорошо',
                                5 => '5 звезд - Отлично',
                            ])
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->rows(4)
                            ->required()
                            ->disabled(fn($record) => $record !== null),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Модерация')
                    ->schema([
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Одобрен')
                            ->default(false)
                            ->visible(fn($record) => $record !== null),

                        Forms\Components\Textarea::make('reply')
                            ->label('Ответ на отзыв')
                            ->rows(3)
                            ->visible(fn($record) => $record !== null)
                            ->helperText('Ответ будет отправлен клиенту'),
                    ])
                    ->visible(fn($record) => $record !== null)
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

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
                    ->formatStateUsing(fn(int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Одобрен')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Оценка')
                    ->options([
                        1 => '1 звезда',
                        2 => '2 звезды',
                        3 => '3 звезды',
                        4 => '4 звезды',
                        5 => '5 звезд',
                    ]),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Статус модерации')
                    ->boolean()
                    ->trueLabel('Одобренные')
                    ->falseLabel('Неодобренные'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Review $record): bool => !$record->is_approved)
                    ->action(function (Review $record) {
                        try {
                            app(UpdateReviewUseCase::class)
                                ->loadData(['id' => $record->id, 'is_approved' => true])
                                ->validate()
                                ->execute();

                            Notification::make()
                                ->title('Отзыв одобрен')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка одобрения')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Review $record): bool => $record->is_approved)
                    ->action(function (Review $record) {
                        try {
                            app(UpdateReviewUseCase::class)
                                ->loadData(['id' => $record->id, 'is_approved' => false])
                                ->validate()
                                ->execute();

                            Notification::make()
                                ->title('Отзыв отклонен')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка отклонения')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->using(function (Review $record) {
                        try {
                            app(DeleteReviewUseCase::class)
                                ->loadData(['id' => $record->id])
                                ->validate()
                                ->execute();

                            Notification::make()
                                ->title('Отзыв удален')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка удаления')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Одобрить выбранные')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $count = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                try {
                                    app(UpdateReviewUseCase::class)
                                        ->loadData(['id' => $record->id, 'is_approved' => true])
                                        ->validate()
                                        ->execute();
                                    $count++;
                                } catch (\Exception $e) {
                                    $errors++;
                                }
                            }

                            if ($count > 0) {
                                Notification::make()
                                    ->title("Одобрено отзывов: {$count}")
                                    ->success()
                                    ->send();
                            }

                            if ($errors > 0) {
                                Notification::make()
                                    ->title("Ошибок: {$errors}")
                                    ->warning()
                                    ->send();
                            }
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function ($records) {
                            $count = 0;
                            $errors = 0;

                            foreach ($records as $record) {
                                try {
                                    app(DeleteReviewUseCase::class)
                                        ->loadData(['id' => $record->id])
                                        ->validate()
                                        ->execute();
                                    $count++;
                                } catch (\Exception $e) {
                                    $errors++;
                                }
                            }

                            if ($count > 0) {
                                Notification::make()
                                    ->title("Удалено отзывов: {$count}")
                                    ->success()
                                    ->send();
                            }

                            if ($errors > 0) {
                                Notification::make()
                                    ->title("Ошибок: {$errors}")
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_deleted', false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
