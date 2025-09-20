<?php

namespace App\Filament\Resources\Manager\ClientResource\RelationManagers;

use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderStatus;
use App\Models\User;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о заказе')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Select::make('type')
                            ->label('Тип услуги')
                            ->options(OrderType::getOptions())
                            ->default(OrderType::REPAIR)
                            ->required(),

                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(OrderStatus::getOptions())
                            ->default(OrderStatus::NEW)
                            ->required(),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options([
                                'low' => 'Низкая',
                                'normal' => 'Обычная',
                                'high' => 'Высокая',
                                'urgent' => 'Срочная',
                            ])
                            ->default('normal'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Описание')
                    ->schema([
                        Forms\Components\Textarea::make('problem_description')
                            ->label('Описание проблемы')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Описание проблемы от клиента'),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Внутренние примечания')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Примечания для внутреннего использования'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Ответственные')
                    ->schema([
                        Forms\Components\Select::make('manager_id')
                            ->label('Менеджер')
                            ->relationship('manager', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('master_id')
                            ->label('Мастер')
                            ->relationship('master', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Общая сумма')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\TextInput::make('final_price')
                            ->label('Итоговая цена')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\TextInput::make('cost_price')
                            ->label('Себестоимость')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Оплачен')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Дата оплаты')
                            ->visible(fn(Forms\Get $get) => $get('is_paid')),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип услуги')
                    ->formatStateUsing(fn(OrderType $state): string => $state->getLabel())
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(OrderStatus $state): string => $state->getLabel())
                    ->color(fn(OrderStatus $state): string => match ($state) {
                        OrderStatus::NEW => 'gray',
                        OrderStatus::CONSULTATION => 'blue',
                        OrderStatus::DIAGNOSTIC => 'yellow',
                        OrderStatus::IN_WORK => 'warning',
                        OrderStatus::WAITING_PARTS => 'orange',
                        OrderStatus::READY => 'success',
                        OrderStatus::ISSUED => 'info',
                        OrderStatus::CANCELLED => 'danger',
                    }),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Срочность')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'low' => 'gray',
                        'normal' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'low' => 'Низкая',
                        'normal' => 'Обычная',
                        'high' => 'Высокая',
                        'urgent' => 'Срочная',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Проблема')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Менеджер')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Не назначен'),

                Tables\Columns\TextColumn::make('master.name')
                    ->label('Мастер')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Не назначен'),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Оплата')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Статус')
                    ->relationship('status', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Срочность')
                    ->options([
                        'low' => 'Низкая',
                        'normal' => 'Обычная',
                        'high' => 'Высокая',
                        'urgent' => 'Срочная',
                    ]),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Оплата')
                    ->placeholder('Все заказы')
                    ->trueLabel('Оплаченные')
                    ->falseLabel('Неоплаченные'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
