<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\BonusTransactionResource\Pages;
use App\Models\BonusTransaction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Forms;

class BonusTransactionResource extends Resource
{
    protected static ?string $model = BonusTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Бонусы';

    protected static ?string $navigationLabel = 'Движения';

    protected static ?int $navigationSort = 11;

    protected static ?string $modelLabel = 'Движение бонусов';

    protected static ?string $pluralModelLabel = 'Движения бонусов';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('client.full_name')->label('Клиент')->searchable(),
                Tables\Columns\TextColumn::make('order_id')->label('Заказ')->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'success' => 'earn',
                        'danger' => 'spend',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'earn' ? 'Начисление' : 'Списание'),
                Tables\Columns\TextColumn::make('amount')->label('Сумма')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Комментарий')->wrap(),
                Tables\Columns\TextColumn::make('idempotency_key')->label('Idempotency')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Дата')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'earn' => 'Начисление',
                        'spend' => 'Списание',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBonusTransactions::route('/'),
            'view' => Pages\ViewBonusTransaction::route('/{record}'),
        ];
    }
}


