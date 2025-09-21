<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static ?string $navigationGroup = 'Справочники';

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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->displayFormat('d.m.Y')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('telegram_verified_at')
                            ->label('Telegram подтвержден')
                            ->disabled(),

                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->disabled()
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
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('telegram')
                    ->label('Telegram')
                    ->formatStateUsing(fn(?string $state): string => $state ? "@{$state}" : '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('telegram_verified_at')
                    ->label('Telegram')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Заказов')
                    ->counts('orders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bonusAccount.balance')
                    ->label('Бонусы')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('telegram_verified_at')
                    ->label('Telegram подтвержден')
                    ->placeholder('Все клиенты')
                    ->trueLabel('Подтвержден')
                    ->falseLabel('Не подтвержден'),

                Tables\Filters\Filter::make('has_orders')
                    ->label('С заказами')
                    ->query(fn(Builder $query): Builder => $query->has('orders')),

                Tables\Filters\Filter::make('birthday_soon')
                    ->label('День рождения скоро')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('birth_date', now()->addDays(7)->month)
                        ->whereDay('birth_date', now()->addDays(7)->day)),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все клиенты')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('view_orders')
                    ->label('Заказы')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn(Client $record): string => route('filament.master.resources.master.orders.index', ['tableFilters[client_id][value]' => $record->id])),
            ])
            ->bulkActions([])
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
            'view' => Pages\ViewClient::route('/{record}'),
        ];
    }

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
