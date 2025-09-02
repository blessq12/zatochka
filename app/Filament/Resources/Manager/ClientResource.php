<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\ClientResource\Pages;
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

    protected static ?string $navigationGroup = 'Управление клиентами';

    protected static ?int $navigationSort = 1;

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
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        Forms\Components\TextInput::make('telegram')
                            ->label('Telegram')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Дата рождения'),

                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->rows(3)
                            ->maxLength(500),
                    ])->columns(2),

                Forms\Components\Section::make('Аккаунт')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),

                        Forms\Components\Toggle::make('is_deleted')
                            ->label('Удален')
                            ->default(false),
                    ])->collapsible(),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('telegram')
                    ->label('Telegram')
                    ->searchable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Заказов')
                    ->counts('orders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bonusTransactions_sum_amount')
                    ->label('Бонусы')
                    ->sum('bonusTransactions', 'amount')
                    ->money('RUB'),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn(Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),

                Tables\Filters\Filter::make('with_orders')
                    ->label('С заказами')
                    ->query(fn(Builder $query): Builder => $query->has('orders')),

                Tables\Filters\Filter::make('with_telegram')
                    ->label('С Telegram')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('telegram')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('orders')
                    ->label('Заказы')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn(Client $record): string => route('filament.admin.resources.manager.orders.index', ['tableFilters[client_id][value]' => $record->id])),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('orders')
            ->withSum('bonusTransactions', 'amount');
    }
}
