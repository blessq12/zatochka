<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Уведомления';
    protected static ?int $navigationSort = 5;

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
                            ->required(),
                        Forms\Components\Select::make('order_id')
                            ->label('Заказ')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Тип')
                            ->options([
                                'ready' => 'Готовность',
                                'reminder' => 'Напоминание',
                                'promo' => 'Акция',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Текст уведомления')
                            ->required()
                            ->maxLength(65535),
                        Forms\Components\Toggle::make('is_sent')
                            ->label('Отправлено'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Номер заказа')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип'),
                Tables\Columns\TextColumn::make('content')
                    ->label('Текст')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_sent')
                    ->label('Отправлено')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'ready' => 'Готовность',
                        'reminder' => 'Напоминание',
                        'promo' => 'Акция',
                    ]),
                Tables\Filters\TernaryFilter::make('is_sent')
                    ->label('Статус отправки'),
                Tables\Filters\SelectFilter::make('client')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
