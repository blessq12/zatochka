<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusSettingResource\Pages;
use App\Models\BonusSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;

class BonusSettingResource extends Resource
{
    protected static ?string $model = BonusSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки бонусов';

    protected static ?string $modelLabel = 'Настройка бонусов';

    protected static ?string $pluralModelLabel = 'Настройки бонусов';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основные настройки')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bonus_percent_per_order')
                                    ->label('Процент начисления бонусов за заказ')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(5)
                                    ->required(),

                                TextInput::make('bonus_exchange_rate')
                                    ->label('Курс обмена бонусов')
                                    ->numeric()
                                    ->suffix('рублей за 1 бонус')
                                    ->default(1)
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('bonus_expiration_months')
                                    ->label('Срок действия бонусов')
                                    ->numeric()
                                    ->suffix('месяцев')
                                    ->default(3)
                                    ->required(),

                                TextInput::make('birthday_bonus_amount')
                                    ->label('Бонусы за день рождения')
                                    ->numeric()
                                    ->suffix('бонусов')
                                    ->default(1000)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Лимиты и условия')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_order_amount_for_bonus')
                                    ->label('Минимальная сумма заказа для начисления')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->default(1500)
                                    ->required(),

                                TextInput::make('min_order_amount_for_spend')
                                    ->label('Минимальная сумма заказа для списания')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->default(3000)
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_bonus_spend_percent')
                                    ->label('Максимальный процент списания от заказа')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(50)
                                    ->required(),

                                TextInput::make('first_review_bonus_amount')
                                    ->label('Бонусы за первый отзыв')
                                    ->numeric()
                                    ->suffix('бонусов')
                                    ->default(1000)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBonusSettings::route('/'),
        ];
    }
}
