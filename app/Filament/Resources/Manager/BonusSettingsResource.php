<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\BonusSettingsResource\Pages;
use App\Models\BonusSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;

class BonusSettingsResource extends Resource
{
    protected static ?string $model = BonusSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Бонусы';

    protected static ?string $navigationLabel = 'Настройки бонусов';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Настройки бонусной системы';

    protected static ?string $pluralModelLabel = 'Настройки бонусной системы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основные настройки бонусов')
                    ->description('Настройте параметры начисления и списания бонусов')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('birthday_bonus')
                                    ->label('Бонус на день рождения')
                                    ->numeric()
                                    ->suffix('бонусов')
                                    ->helperText('Количество бонусов, начисляемых клиенту в день рождения')
                                    ->required(),

                                TextInput::make('first_order_bonus')
                                    ->label('Бонус за первый заказ')
                                    ->numeric()
                                    ->suffix('бонусов')
                                    ->helperText('Количество бонусов за первый заказ клиента')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('percent_per_order')
                                    ->label('Процент за заказ')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Процент от суммы заказа, который начисляется в бонусы')
                                    ->required(),

                                TextInput::make('max_bonus_per_order')
                                    ->label('Максимум бонусов за заказ')
                                    ->numeric()
                                    ->suffix('бонусов')
                                    ->helperText('Максимальное количество бонусов, которое можно получить за один заказ')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Настройки конвертации')
                    ->description('Настройте курс обмена бонусов на рубли')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('rate')
                                    ->label('Курс обмена (руб/бонус)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('₽')
                                    ->helperText('Сколько рублей стоит один бонус')
                                    ->required(),

                                Placeholder::make('example_conversion')
                                    ->label('Пример конвертации')
                                    ->content(function ($get) {
                                        $rate = $get('rate') ?: 1;
                                        return "100 бонусов = " . number_format(100 * $rate, 2) . " ₽";
                                    }),
                            ]),
                    ]),

                Section::make('Ограничения использования')
                    ->description('Настройте условия для использования бонусов')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_order_amount')
                                    ->label('Минимальная сумма заказа для начисления')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('₽')
                                    ->helperText('Минимальная сумма заказа для начисления бонусов')
                                    ->required(),

                                TextInput::make('min_order_sum_for_spending')
                                    ->label('Минимальная сумма для списания бонусов')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('₽')
                                    ->helperText('Минимальная сумма заказа для возможности списания бонусов')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('expire_days')
                                    ->label('Срок действия бонусов')
                                    ->numeric()
                                    ->suffix('дней')
                                    ->helperText('Количество дней, в течение которых действуют бонусы')
                                    ->required(),

                                Placeholder::make('expire_info')
                                    ->label('Информация о сроке действия')
                                    ->content(function ($get) {
                                        $days = $get('expire_days') ?: 365;
                                        return "Бонусы будут действовать {$days} дней с момента начисления";
                                    }),
                            ]),
                    ]),


            ]);
    }

    // Убираем таблицу, так как у нас только одна запись настроек

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditBonusSettings::route('/'),
        ];
    }
}
