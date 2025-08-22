<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Отзывы';

    protected static ?string $modelLabel = 'Отзыв';

    protected static ?string $pluralModelLabel = 'Отзывы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Тип отзыва')
                            ->options([
                                'feedback' => 'Отзыв о заказе',
                                'testimonial' => 'Отзыв о сервисе'
                            ])
                            ->required()
                            ->default('testimonial')
                            ->reactive(),

                        Forms\Components\Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn($get) => $get('type') === 'feedback'),

                        Forms\Components\Select::make('target_model')
                            ->label('Целевая модель')
                            ->options([
                                'App\Models\User' => 'Пользователи',
                                'App\Models\Order' => 'Заказы',
                                'App\Models\Client' => 'Клиенты',
                                'App\Models\Company' => 'Компании',
                                'App\Models\Branch' => 'Филиалы',
                                'App\Models\Tool' => 'Инструменты',
                                'App\Models\Repair' => 'Ремонты',
                            ])
                            ->nullable()
                            ->visible(fn($get) => $get('type') === 'testimonial')
                            ->reactive(),

                        Forms\Components\Select::make('target_record')
                            ->label('Выберите запись')
                            ->options(function ($get) {
                                $model = $get('target_model');
                                if (!$model || !class_exists($model)) {
                                    return [];
                                }

                                $records = $model::limit(100)->get();
                                $options = [];

                                foreach ($records as $record) {
                                    $label = match ($model) {
                                        'App\Models\User' => "Пользователь: {$record->name}",
                                        'App\Models\Order' => "Заказ: {$record->order_number}",
                                        'App\Models\Client' => "Клиент: {$record->full_name}",
                                        'App\Models\Company' => "Компания: {$record->name}",
                                        'App\Models\Branch' => "Филиал: {$record->name}",
                                        'App\Models\Tool' => "Инструмент: {$record->name}",
                                        'App\Models\Repair' => "Ремонт: {$record->id}",
                                        default => "ID: {$record->id}"
                                    };
                                    $options[$record->id] = $label;
                                }

                                return $options;
                            })
                            ->nullable()
                            ->visible(fn($get) => $get('type') === 'testimonial' && $get('target_model')),

                        Forms\Components\Select::make('rating')
                            ->label('Рейтинг')
                            ->options([
                                1 => '1 звезда - Ужасно',
                                2 => '2 звезды - Плохо',
                                3 => '3 звезды - Нормально',
                                4 => '4 звезды - Хорошо',
                                5 => '5 звезд - Отлично'
                            ])
                            ->nullable(),

                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий')
                            ->required()
                            ->maxLength(2000)
                            ->rows(4),

                        Forms\Components\Select::make('source')
                            ->label('Источник')
                            ->options([
                                'website' => 'Сайт',
                                'telegram' => 'Telegram',
                                'api' => 'API',
                                'external' => 'Внешний источник'
                            ])
                            ->required()
                            ->default('website'),

                        Forms\Components\Textarea::make('reply')
                            ->label('Ответ на отзыв')
                            ->maxLength(1000)
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Метаданные')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending' => 'На модерации',
                                'approved' => 'Одобрен',
                                'rejected' => 'Отклонен'
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'feedback' => 'Отзыв о заказе',
                        'testimonial' => 'Отзыв о сервисе',
                        default => $state
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'feedback' => 'info',
                        'testimonial' => 'success',
                        default => 'gray'
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Рейтинг')
                    ->formatStateUsing(fn(int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Источник')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'website' => 'Сайт',
                        'telegram' => 'Telegram',
                        'api' => 'API',
                        'external' => 'Внешний',
                        default => $state
                    })
                    ->badge()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'На модерации',
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонен',
                        default => $state
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип отзыва')
                    ->options([
                        'feedback' => 'Отзыв о заказе',
                        'testimonial' => 'Отзыв о сервисе'
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На модерации',
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонен'
                    ]),

                Tables\Filters\SelectFilter::make('source')
                    ->label('Источник')
                    ->options([
                        'website' => 'Сайт',
                        'telegram' => 'Telegram',
                        'api' => 'API',
                        'external' => 'Внешний источник'
                    ]),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('Рейтинг')
                    ->options([
                        1 => '1 звезда',
                        2 => '2 звезды',
                        3 => '3 звезды',
                        4 => '4 звезды',
                        5 => '5 звезд'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Review $record): bool => $record->isPending())
                    ->action(fn(Review $record) => $record->approve())
                    ->requiresConfirmation()
                    ->modalHeading('Одобрить отзыв?')
                    ->modalDescription('Этот отзыв будет опубликован на сайте.')
                    ->modalSubmitActionLabel('Да, одобрить'),

                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(Review $record): bool => $record->isPending())
                    ->action(fn(Review $record) => $record->reject())
                    ->requiresConfirmation()
                    ->modalHeading('Отклонить отзыв?')
                    ->modalDescription('Этот отзыв не будет опубликован.')
                    ->modalSubmitActionLabel('Да, отклонить'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Одобрить выбранные')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn(Collection $records) => $records->each->approve())
                        ->requiresConfirmation()
                        ->modalHeading('Одобрить отзывы?')
                        ->modalDescription('Выбранные отзывы будут опубликованы на сайте.')
                        ->modalSubmitActionLabel('Да, одобрить'),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Отклонить выбранные')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn(Collection $records) => $records->each->reject())
                        ->requiresConfirmation()
                        ->modalHeading('Отклонить отзывы?')
                        ->modalDescription('Выбранные отзывы не будут опубликованы.')
                        ->modalSubmitActionLabel('Да, отклонить'),

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
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    // Метод для обработки данных формы перед сохранением
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Если выбран target_record, устанавливаем entity_id и entity_type
        if (isset($data['target_record']) && isset($data['target_model'])) {
            $data['entity_id'] = $data['target_record'];
            $data['entity_type'] = $data['target_model'];
        }

        // Удаляем временные поля
        unset($data['target_record'], $data['target_model']);

        return $data;
    }

    // Метод для обработки данных формы перед обновлением
    public static function mutateFormDataBeforeUpdate(array $data): array
    {
        return static::mutateFormDataBeforeCreate($data);
    }
}
