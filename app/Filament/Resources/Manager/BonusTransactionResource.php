<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\BonusTransactionResource\Pages;
use App\Filament\Resources\Manager\BonusTransactionResource\RelationManagers;
use App\Models\BonusTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BonusTransactionResource extends Resource
{
    protected static ?string $model = BonusTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Бонусная система';
    protected static ?string $pluralLabel = 'Движения бонусов';
    protected static ?string $modelLabel = 'Движение бонусов';


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

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
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(int $state): string => $state . ' бонусов'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип операции')
                    ->options([
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                    ]),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Убираем EditAction - нельзя редактировать транзакции
            ])
            ->bulkActions([
                // Убираем BulkActions - нельзя удалять транзакции
            ])
            ->emptyStateHeading('Нет движений бонусов')
            ->emptyStateDescription('Движения бонусов будут отображаться здесь');
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
            'index' => Pages\ListBonusTransactions::route('/'),
            'view' => Pages\ViewBonusTransaction::route('/{record}'),
        ];
    }
}
