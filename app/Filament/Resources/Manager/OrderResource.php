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
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderUrgency;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

                        Forms\Components\Select::make('type')
                            ->label('Тип услуги')
                            ->options(OrderType::getOptions())
                            ->default(OrderType::REPAIR)
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
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id())
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

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(OrderStatus::getOptions())
                            ->default(OrderStatus::NEW)
                            ->required(),

                        Forms\Components\Select::make('urgency')
                            ->label('Срочность')
                            ->options(OrderUrgency::getOptions())
                            ->default(OrderUrgency::NORMAL)
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

                Forms\Components\Section::make('Фотографии')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('before_photos')
                            ->label('Фото "До" (что принес клиент)')
                            ->collection('before_photos')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Загрузите фотографии устройства/проблемы до начала работ'),

                        SpatieMediaLibraryFileUpload::make('after_photos')
                            ->label('Фото "После" (результат работ)')
                            ->collection('after_photos')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Загрузите фотографии результата работ (можно добавить позже)'),
                    ])
                    ->columns(1)
                    ->collapsible(),
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
                    ->formatStateUsing(fn(OrderUrgency $state): string => $state->getLabel())
                    ->color(fn(OrderUrgency $state): string => $state->getColor()),

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

                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Фото')
                    ->formatStateUsing(function ($record) {
                        $beforeCount = $record->getMedia('before_photos')->count();
                        $afterCount = $record->getMedia('after_photos')->count();
                        $total = $beforeCount + $afterCount;

                        if ($total === 0) {
                            return 'Нет фото';
                        }

                        return "📷 {$total} ({$beforeCount} до, {$afterCount} после)";
                    })
                    ->badge()
                    ->color(fn($state) => str_contains($state, 'Нет') ? 'gray' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(OrderStatus::getOptions()),

                Tables\Filters\SelectFilter::make('urgency')
                    ->label('Срочность')
                    ->options(OrderUrgency::getOptions()),

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
                Tables\Actions\ViewAction::make()
                    ->url(fn(Order $record): string => static::getUrl('view', ['record' => $record])),
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
            RelationManagers\ActivityLogsRelationManager::class,
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
}
