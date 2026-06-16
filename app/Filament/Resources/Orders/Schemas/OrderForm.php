<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Infrastructure\ClientPortal\Persistence\Eloquent\ClientModel;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                CheckboxList::make('service_types')
                    ->label('Тип услуги')
                    ->options([
                        'sharpening' => 'Заточка',
                        'repair' => 'Ремонт',
                    ])
                    ->required()
                    ->columns(2),
                Select::make('urgency')
                    ->label('Срочность')
                    ->options([
                        OrderUrgency::Standard->value => 'Стандарт',
                        OrderUrgency::Urgent->value => 'Срочно',
                    ])
                    ->default(OrderUrgency::Standard->value),
                Toggle::make('needs_delivery')
                    ->label('Нужна доставка')
                    ->default(false),
                TextInput::make('delivery_address')
                    ->label('Адрес доставки')
                    ->maxLength(255),
                Textarea::make('problem_description')
                    ->label('Описание проблемы')
                    ->rows(3),
                Select::make('client_id')
                    ->label('Клиент (ЛК)')
                    ->searchable()
                    ->nullable()
                    ->options(fn (): array => ClientModel::query()
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                        ->all()),
                TextInput::make('client_full_name')
                    ->label('Имя клиента (гость)')
                    ->maxLength(255)
                    ->requiredWithout('client_id'),
                TextInput::make('client_phone')
                    ->label('Телефон клиента')
                    ->tel()
                    ->maxLength(32)
                    ->requiredWithout('client_id'),
            ]);
    }
}
