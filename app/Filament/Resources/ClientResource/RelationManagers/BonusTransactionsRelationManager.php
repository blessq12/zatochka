<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BonusTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'bonusTransactions';

    protected static ?string $title = 'История бонусных транзакций';

    protected static ?string $modelLabel = 'Транзакция';

    protected static ?string $pluralModelLabel = 'Транзакции';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Форма не используется - транзакции создаются автоматически
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'earn' => 'success',
                        'spend' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->badge()
                    ->color(fn($record) => $record->type === 'earn' ? 'success' : 'danger')
                    ->formatStateUsing(fn($record) => ($record->type === 'earn' ? '+' : '-') . $record->amount)
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => $record->order 
                        ? \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record->order])
                        : null)
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип транзакции')
                    ->options([
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                    ]),
            ])
            ->headerActions([
                // Транзакции создаются автоматически, нельзя создавать вручную
            ])
            ->actions([
                // Транзакции только для просмотра
            ])
            ->bulkActions([
                // Нет массовых действий
            ])
            ->defaultSort('created_at', 'desc');
    }
}
