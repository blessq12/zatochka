<?php

namespace App\Filament\Resources\Manager;

use App\Application\UseCases\Order\CreateOrderUseCase;
use App\Application\UseCases\Order\UpdateOrderUseCase;
use App\Application\UseCases\Order\DeleteOrderUseCase;
use App\Domain\Order\Exception\OrderException;
use App\Filament\Resources\Manager\OrderResource\Pages;
use App\Filament\Resources\Manager\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Client;
use App\Models\Branch;
use App\Models\ServiceType;
use App\Models\OrderStatus;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Заказы';
    protected static ?string $pluralLabel = 'Заказы';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('full_name')
                                    ->label('ФИО клиента')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('telegram')
                                    ->label('Telegram')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Select::make('service_type_id')
                            ->label('Тип услуги')
                            ->relationship('serviceType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('branch_id')
                            ->label('Филиал')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn() => \App\Models\Branch::where('is_main', true)->first()?->id)
                            ->required(),

                        Forms\Components\Select::make('manager_id')
                            ->label('Менеджер')
                            ->relationship('manager', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->required(),

                        Forms\Components\Select::make('master_id')
                            ->label('Мастер')
                            ->relationship('master', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Детали заказа')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status_id')
                            ->label('Статус')
                            ->relationship('status', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options([
                                'low' => 'Низкая',
                                'normal' => 'Обычная',
                                'high' => 'Высокая',
                                'urgent' => 'Срочная',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание проблемы')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Примечания')
                            ->rows(2)
                            ->columnSpanFull(),
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

                        Forms\Components\TextInput::make('profit')
                            ->label('Прибыль')
                            ->numeric()
                            ->prefix('₽')
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(false),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Тип услуги')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Новый' => 'gray',
                        'В работе' => 'warning',
                        'Готов' => 'success',
                        'Отменен' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Срочность')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'normal' => 'success',
                        'low' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'urgent' => 'Срочная',
                        'high' => 'Высокая',
                        'normal' => 'Обычная',
                        'low' => 'Низкая',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Менеджер')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('master.name')
                    ->label('Мастер')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Оплачен')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Статус')
                    ->relationship('status', 'name'),

                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Срочность')
                    ->options([
                        'low' => 'Низкая',
                        'normal' => 'Обычная',
                        'high' => 'Высокая',
                        'urgent' => 'Срочная',
                    ]),

                Tables\Filters\SelectFilter::make('manager_id')
                    ->label('Менеджер')
                    ->relationship('manager', 'name'),

                Tables\Filters\SelectFilter::make('master_id')
                    ->label('Мастер')
                    ->relationship('master', 'name'),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Оплачен')
                    ->boolean()
                    ->trueLabel('Оплачен')
                    ->falseLabel('Не оплачен')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(function (Order $record) {
                        try {
                            (new DeleteOrderUseCase())
                                ->loadData(['id' => $record->id])
                                ->validate()
                                ->execute();

                            Notification::make()
                                ->title('Заказ удален')
                                ->success()
                                ->send();
                        } catch (OrderException $e) {
                            Notification::make()
                                ->title('Ошибка удаления')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function ($records) {
                            foreach ($records as $record) {
                                try {
                                    (new DeleteOrderUseCase())
                                        ->loadData(['id' => $record->id])
                                        ->validate()
                                        ->execute();
                                } catch (OrderException $e) {
                                    Notification::make()
                                        ->title('Ошибка удаления заказа #' . $record->order_number)
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }
                        }),
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
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
