<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Заказы';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->disabled(),

                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->disabled(),

                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->relationship('branch', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('type')
                            ->label('Тип заказа')
                            ->options(Order::getAvailableTypes())
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(Order::getAvailableStatuses())
                            ->disabled(),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options(Order::getAvailableUrgencies())
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('estimated_price')
                            ->label('Ориентировочная цена')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled(),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('Фактическая цена')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')
                            ->label('Описание проблемы')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Внутренние заметки')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableTypes()[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::TYPE_REPAIR => 'warning',
                        Order::TYPE_SHARPENING => 'success',
                        Order::TYPE_DIAGNOSTIC => 'info',
                        Order::TYPE_REPLACEMENT => 'primary',
                        Order::TYPE_MAINTENANCE => 'secondary',
                        Order::TYPE_CONSULTATION => 'gray',
                        Order::TYPE_WARRANTY => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableStatuses()[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::STATUS_NEW => 'primary',
                        Order::STATUS_CONSULTATION => 'warning',
                        Order::STATUS_DIAGNOSTIC => 'info',
                        Order::STATUS_IN_WORK => 'secondary',
                        Order::STATUS_WAITING_PARTS => 'danger',
                        Order::STATUS_READY => 'success',
                        Order::STATUS_ISSUED => 'gray',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Срочность')
                    ->formatStateUsing(fn(string $state): string => Order::getAvailableUrgencies()[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Order::URGENCY_NORMAL => 'gray',
                        Order::URGENCY_URGENT => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('estimated_price')
                    ->label('Ориентировочная цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('Фактическая цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::getAvailableStatuses()),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options(Order::getAvailableTypes()),

                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Срочность')
                    ->options(Order::getAvailableUrgencies()),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->relationship('branch', 'name'),

                Tables\Filters\TernaryFilter::make('is_deleted')
                    ->label('Статус')
                    ->placeholder('Все заказы')
                    ->trueLabel('Только удаленные')
                    ->falseLabel('Только активные'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('create_repair')
                    ->label('Создать ремонт')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('success')
                    ->url(fn(Order $record): string => route('filament.master.resources.master.repairs.create', ['order_id' => $record->id]))
                    ->visible(fn(Order $record): bool => !$record->repair),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
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
