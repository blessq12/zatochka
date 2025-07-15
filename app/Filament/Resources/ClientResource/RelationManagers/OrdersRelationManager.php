<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->label('Номер заказа')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'new' => 'Новый',
                        'in_progress' => 'В работе',
                        'ready' => 'Готов',
                        'delivered' => 'Доставлен',
                        'cancelled' => 'Отменен',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Сумма')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('cost_price')
                    ->label('Себестоимость')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('profit')
                    ->label('Прибыль')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Сумма')
                    ->money('RUB'),
                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Себестоимость')
                    ->money('RUB'),
                Tables\Columns\TextColumn::make('profit')
                    ->label('Прибыль')
                    ->money('RUB'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime(),
            ])
            ->filters([
                //
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
