<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusTransactionResource\Pages;
use App\Filament\Resources\BonusTransactionResource\RelationManagers;
use App\Models\BonusTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\BadgeColumn;

class BonusTransactionResource extends Resource
{
    protected static ?string $model = BonusTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Транзакции бонусов';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Транзакция бонусов';

    protected static ?string $pluralModelLabel = 'Транзакции бонусов';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->required(),

                Select::make('order_id')
                    ->label('Заказ')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->nullable(),

                Select::make('type')
                    ->label('Тип операции')
                    ->options([
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->prefix('₽')
                    ->required(),

                Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client.phone')
                    ->label('Телефон')
                    ->searchable(),

                TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'success' => 'earn',
                        'danger' => 'spend',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                    }),

                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->color(fn(BonusTransaction $record): string => $record->isEarn() ? 'success' : 'danger'),

                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип операции')
                    ->options([
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                    ]),

                Tables\Filters\Filter::make('has_order')
                    ->label('С заказом')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('order_id')),

                Tables\Filters\Filter::make('no_order')
                    ->label('Без заказа')
                    ->query(fn(Builder $query): Builder => $query->whereNull('order_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListBonusTransactions::route('/'),
            'create' => Pages\CreateBonusTransaction::route('/create'),
            'edit' => Pages\EditBonusTransaction::route('/{record}/edit'),
        ];
    }
}
