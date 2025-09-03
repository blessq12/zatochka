<?php

namespace App\Filament\Resources\Master;

use App\Filament\Resources\Master\OrderResource\Pages;
use App\Models\Order;
use App\Models\Client;
use App\Models\Branch;
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

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Мои заказы';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о заказе')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Номер заказа')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('client.full_name')
                            ->label('Клиент')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('client.phone')
                            ->label('Телефон клиента')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('branch.name')
                            ->label('Филиал')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('serviceType.name')
                            ->label('Тип услуги')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options([
                                'urgent' => 'Срочно',
                                'normal' => 'Обычно',
                            ])
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Статус работы')
                    ->schema([
                        Forms\Components\Select::make('status_id')
                            ->label('Статус заказа')
                            ->options(OrderStatus::where('type', 'master')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Textarea::make('work_notes')
                            ->label('Заметки по работе')
                            ->rows(3)
                            ->maxLength(1000),
                    ])->columns(1),

                Forms\Components\Section::make('Финансовая информация')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Сумма заказа')
                            ->prefix('₽')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('final_price')
                            ->label('Итоговая цена')
                            ->prefix('₽')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Оплачен')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),
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
                    ->options(OrderStatus::where('type', 'master')->pluck('name', 'id')),

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
                Tables\Actions\Action::make('repairs')
                    ->label('Работы')
                    ->icon('heroicon-o-wrench')
                    ->url(fn (Order $record): string => route('filament.admin.resources.master.repairs.index', ['tableFilters[order_id][value]' => $record->id])),

                Tables\Actions\Action::make('inventory')
                    ->label('Материалы')
                    ->icon('heroicon-o-cube')
                    ->url(fn (Order $record): string => route('filament.admin.resources.master.inventory-transactions.index', ['tableFilters[order_id][value]' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_in_progress')
                        ->label('Отметить "В работе"')
                        ->icon('heroicon-o-play')
                        ->color('warning')
                        ->action(function ($records): void {
                            $statusId = OrderStatus::where('name', 'В работе')->first()?->id;
                            if ($statusId) {
                                $records->each(function ($record) use ($statusId) {
                                    $record->update(['status_id' => $statusId]);
                                });
                            }
                        }),

                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Отметить "Готов"')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records): void {
                            $statusId = OrderStatus::where('name', 'Готов')->first()?->id;
                            if ($statusId) {
                                $records->each(function ($record) use ($statusId) {
                                    $record->update(['status_id' => $statusId]);
                                });
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
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('master_id', fn () => auth()->id())
            ->with(['client', 'branch', 'serviceType', 'status']);
    }
}
