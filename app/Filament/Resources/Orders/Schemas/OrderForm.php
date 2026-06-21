<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Identity\Enum\UserRole;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Filament\Support\OrderFormCommandBuilder;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Параметры заказа')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->description('Тип услуги, срочность и вид оплаты')
                    ->columns(2)
                    ->schema([
                        Select::make('service_type')
                            ->label('Тип заказа')
                            ->options(OrderFormCommandBuilder::SERVICE_TYPE_OPTIONS)
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        Select::make('urgency')
                            ->label('Срочность')
                            ->options([
                                OrderUrgency::Standard->value => 'Обычный',
                                OrderUrgency::Urgent->value => 'Срочный',
                            ])
                            ->default(OrderUrgency::Standard->value)
                            ->required(),

                        Toggle::make('is_warranty')
                            ->label('Гарантийный заказ')
                            ->default(false)
                            ->live(),

                        Select::make('warranty_parent_order_id')
                            ->label('Исходный заказ (референс)')
                            ->searchable()
                            ->options(fn (): array => OrderModel::query()
                                ->orderByDesc('id')
                                ->limit(200)
                                ->get(['id', 'order_number'])
                                ->mapWithKeys(fn (OrderModel $order): array => [
                                    $order->id => $order->order_number,
                                ])
                                ->all())
                            ->required(fn (Get $get): bool => (bool) $get('is_warranty'))
                            ->visible(fn (Get $get): bool => (bool) $get('is_warranty'))
                            ->columnSpanFull(),
                    ]),

                Section::make('Клиент')
                    ->icon('heroicon-o-user-circle')
                    ->description('Существующий аккаунт ЛК или новый гость')
                    ->columns(2)
                    ->schema([
                        Select::make('client_mode')
                            ->label('Способ указания')
                            ->options([
                                'existing' => 'Существующий (ЛК)',
                                'guest' => 'Новый (гость)',
                            ])
                            ->default('guest')
                            ->required()
                            ->live()
                            ->columnSpanFull(),

                        Select::make('client_id')
                            ->label('Клиент из ЛК')
                            ->searchable()
                            ->options(fn (): array => ClientModel::query()
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->required(fn (Get $get): bool => $get('client_mode') === 'existing')
                            ->visible(fn (Get $get): bool => $get('client_mode') === 'existing')
                            ->columnSpanFull(),

                        TextInput::make('client_full_name')
                            ->label('Имя клиента')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('client_mode') === 'guest')
                            ->visible(fn (Get $get): bool => $get('client_mode') === 'guest'),

                        TextInput::make('client_phone')
                            ->label('Телефон клиента')
                            ->tel()
                            ->maxLength(32)
                            ->required(fn (Get $get): bool => $get('client_mode') === 'guest')
                            ->visible(fn (Get $get): bool => $get('client_mode') === 'guest'),
                    ]),

                Section::make('Ответственные')
                    ->icon('heroicon-o-users')
                    ->description('Менеджер обязателен. Мастера можно назначить позже — до этого заказ не появится в POS')
                    ->columns(2)
                    ->schema([
                        Select::make('manager_id')
                            ->label('Менеджер')
                            ->options(fn (): array => self::userOptions(UserRole::Manager))
                            ->required()
                            ->searchable(),

                        Select::make('master_id')
                            ->label('Мастер')
                            ->placeholder('Назначить позже')
                            ->options(fn (): array => self::userOptions(UserRole::Master))
                            ->searchable(),
                    ]),

                Section::make('Предмет работы')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->description('Заполняется в зависимости от типа заказа')
                    ->visible(fn (Get $get): bool => in_array($get('service_type'), ['repair', 'sharpening'], true))
                    ->columns(2)
                    ->schema([
                        Select::make('equipment_mode')
                            ->label('Способ указания оборудования')
                            ->options([
                                'existing' => 'Выбрать из справочника',
                                'new' => 'Создать новое',
                            ])
                            ->default('existing')
                            ->required()
                            ->live()
                            ->visible(fn (Get $get): bool => $get('service_type') === 'repair')
                            ->columnSpanFull(),

                        Select::make('equipment_id')
                            ->label('Оборудование')
                            ->searchable()
                            ->options(fn (): array => EquipmentModel::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (EquipmentModel $equipment): array => [
                                    $equipment->id => trim($equipment->name.' '.($equipment->brand ?? '').' '.($equipment->model ?? '')),
                                ])
                                ->all())
                            ->required(fn (Get $get): bool => $get('service_type') === 'repair'
                                && $get('equipment_mode') === 'existing')
                            ->visible(fn (Get $get): bool => $get('service_type') === 'repair'
                                && $get('equipment_mode') === 'existing')
                            ->columnSpanFull(),

                        TextInput::make('equipment_name')
                            ->label('Название оборудования')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => $get('service_type') === 'repair'
                                && $get('equipment_mode') === 'new')
                            ->visible(fn (Get $get): bool => $get('service_type') === 'repair'
                                && $get('equipment_mode') === 'new'),

                        TextInput::make('equipment_brand')
                            ->label('Бренд')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('service_type') === 'repair'
                                && $get('equipment_mode') === 'new'),

                        TextInput::make('equipment_model')
                            ->label('Модель')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('service_type') === 'repair'
                                && $get('equipment_mode') === 'new'),

                        Repeater::make('tools')
                            ->label('Инструменты')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Наименование')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('tool_type')
                                    ->label('Тип инструмента')
                                    ->options(OrderFormCommandBuilder::TOOL_TYPE_OPTIONS)
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->default(1),
                            ])
                            ->defaultItems(1)
                            ->minItems(1)
                            ->columns(3)
                            ->visible(fn (Get $get): bool => $get('service_type') === 'sharpening')
                            ->columnSpanFull(),
                    ]),

                Section::make('Доставка и описание')
                    ->icon('heroicon-o-truck')
                    ->description('Логистика и текстовое описание проблемы')
                    ->columns(2)
                    ->schema([
                        Toggle::make('needs_delivery')
                            ->label('Нужна доставка')
                            ->default(false)
                            ->live(),

                        TextInput::make('delivery_address')
                            ->label('Адрес доставки')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => (bool) $get('needs_delivery'))
                            ->columnSpanFull(),

                        Textarea::make('problem_description')
                            ->label('Описание / проблема')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /** @return array<int, string> */
    private static function userOptions(UserRole $role): array
    {
        return UserModel::query()
            ->where('role', $role)
            ->orderBy('name')
            ->get(['id', 'name', 'surname'])
            ->mapWithKeys(fn (UserModel $user): array => [
                $user->id => trim($user->name.' '.$user->surname),
            ])
            ->all();
    }
}
