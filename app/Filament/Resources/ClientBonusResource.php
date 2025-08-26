<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientBonusResource\Pages;
use App\Filament\Resources\ClientBonusResource\RelationManagers;
use App\Models\ClientBonus;
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
use Filament\Forms\Components\DateTimePicker;

class ClientBonusResource extends Resource
{
    protected static ?string $model = ClientBonus::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Бонусы клиентов';

    protected static ?string $navigationGroup = 'Система лояльности';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Бонусы клиента';

    protected static ?string $pluralModelLabel = 'Бонусы клиентов';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->required(),

                TextInput::make('balance')
                    ->label('Текущий баланс')
                    ->numeric()
                    ->prefix('₽')
                    ->required(),

                TextInput::make('total_earned')
                    ->label('Всего начислено')
                    ->numeric()
                    ->prefix('₽')
                    ->required(),

                TextInput::make('total_spent')
                    ->label('Всего списано')
                    ->numeric()
                    ->prefix('₽')
                    ->required(),

                DateTimePicker::make('expires_at')
                    ->label('Срок действия')
                    ->nullable(),
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

                TextColumn::make('balance')
                    ->label('Баланс')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('total_earned')
                    ->label('Начислено')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('total_spent')
                    ->label('Списано')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Срок действия')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_balance')
                    ->label('С балансом')
                    ->query(fn(Builder $query): Builder => $query->where('balance', '>', 0)),
            ])
            ->actions([
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
            'index' => Pages\ListClientBonuses::route('/'),
            'create' => Pages\CreateClientBonus::route('/create'),
            'edit' => Pages\EditClientBonus::route('/{record}/edit'),
        ];
    }
}
