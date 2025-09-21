<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\ClientResource\Pages;
use App\Filament\Resources\Master\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Клиенты';
    protected static ?string $pluralLabel = 'Клиенты';
    protected static ?string $modelLabel = 'Клиент';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_deleted', false)
            ->with('bonusAccount');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('ФИО')
                            ->disabled(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->disabled(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled(),

                        Forms\Components\TextInput::make('telegram')
                            ->label('Telegram')
                            ->disabled(),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->disabled()
                            ->displayFormat('d.m.Y'),

                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('telegram')
                    ->label('Telegram')
                    ->searchable()
                    ->copyable()
                    ->formatStateUsing(function ($state) {
                        return $state ? '@' . $state : '';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bonusAccount.balance')
                    ->label('Бонусы')
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format($state) . ' бон.' : '0 бон.';
                    })
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('is_deleted')
                    ->label('Статус')
                    ->formatStateUsing(fn($state) => $state ? 'Удален' : 'Активен')
                    ->badge()
                    ->color(fn($state) => $state ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все клиенты')
                    ->trueLabel('Удаленные')
                    ->falseLabel('Активные')
                    ->queries(
                        true: fn(Builder $query) => $query->where('is_deleted', true),
                        false: fn(Builder $query) => $query->where('is_deleted', false),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Нет bulk actions для read-only ресурса
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'view' => Pages\ViewClient::route('/{record}'),
        ];
    }


    // Запрещаем создание и редактирование
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
