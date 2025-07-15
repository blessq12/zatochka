<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'order_number')
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->dateTime(),
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
