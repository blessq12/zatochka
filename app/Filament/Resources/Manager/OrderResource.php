<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\OrderResource\Pages;
use App\Models\Order;
use App\Models\Client;
use App\Models\Branch;
use App\Models\User;
use App\Models\ServiceType;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Управление заказами';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->options(Client::active()->pluck('full_name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('order_number', null)),

                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->default(fn () => 'ORD-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT)),

                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->options(Branch::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('service_type_id')
                            ->label('Тип услуги')
                            ->options(ServiceType::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options([
                                'normal' => 'Обычная',
                                'urgent' => 'Срочная',
                            ])
                            ->default('normal')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Назначение')
                    ->schema([
                        Forms\Components\Select::make('manager_id')
                            ->label('Менеджер')
                            ->options(User::role('manager')->pluck('name', 'id'))
                            ->searchable()
                            ->default(fn () => auth()->id())
                            ->required(),

                        Forms\Components\Select::make('master_id')
                            ->label('Мастер')
                            ->options(User::role('master')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Select::make('status_id')
                            ->label('Статус')
                            ->options(OrderStatus::where('type', 'manager')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Финансы')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Сумма заказа')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),

                        Forms\Components\Select::make('discount_id')
                            ->label('Скидка')
                            ->options(\App\Models\DiscountRule::active()->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\TextInput::make('final_price')
                            ->label('Итоговая цена')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),

                        Forms\Components\TextInput::make('cost_price')
                            ->label('Себестоимость')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),

                        Forms\Components\TextInput::make('profit')
                            ->label('Прибыль')
                            ->numeric()
                            ->prefix('₽')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Оплачен')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Дата оплаты')
                            ->nullable(),
                    ])->columns(3),

                Forms\Components\Section::make('Статус')
                    ->schema([
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
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Филиал')
                    ->sortable(),

                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Услуга')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('urgency')
                    ->label('Срочность')
                    ->colors([
                        'warning' => 'urgent',
                        'gray' => 'normal',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'urgent' => 'Срочно',
                        'normal' => 'Обычно',
                    }),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Новый' => 'gray',
                        'В работе' => 'warning',
                        'Готов' => 'success',
                        'Отменен' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('master.name')
                    ->label('Мастер')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('Сумма')
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
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Только активные')
                    ->query(fn (Builder $query): Builder => $query->where('is_deleted', false))
                    ->default(),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Статус')
                    ->options(OrderStatus::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Филиал')
                    ->options(Branch::active()->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('master_id')
                    ->label('Мастер')
                    ->options(User::role('master')->pluck('name', 'id')),

                Tables\Filters\Filter::make('urgent')
                    ->label('Срочные заказы')
                    ->query(fn (Builder $query): Builder => $query->where('urgency', 'urgent')),

                Tables\Filters\Filter::make('unpaid')
                    ->label('Неоплаченные')
                    ->query(fn (Builder $query): Builder => $query->where('is_paid', false)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign_master')
                    ->label('Назначить мастера')
                    ->icon('heroicon-o-user')
                    ->form([
                        Forms\Components\Select::make('master_id')
                            ->label('Мастер')
                            ->options(User::role('master')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update($data);
                    })
                    ->visible(fn (Order $record): bool => !$record->master_id),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'branch', 'master', 'serviceType', 'status']);
    }
}
