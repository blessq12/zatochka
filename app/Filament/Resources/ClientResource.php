<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
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
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?string $navigationLabel = 'Клиенты';
    protected static ?int $navigationSort = 1;
    protected static ?string $breadcrumb = 'Клиенты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('telegram')
                            ->label('Telegram')
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->format('Y-m-d'),
                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->maxLength(65535),
                    ])->columns(2),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('telegram')
                    ->label('Telegram')
                    ->searchable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Кол-во заказов')
                    ->counts('orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_orders')
                    ->label('С заказами')
                    ->query(fn(Builder $query): Builder => $query->has('orders')),
                Tables\Filters\Filter::make('no_orders')
                    ->label('Без заказов')
                    ->query(fn(Builder $query): Builder => $query->doesntHave('orders')),
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
            RelationManagers\OrdersRelationManager::class,

            RelationManagers\NotificationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
