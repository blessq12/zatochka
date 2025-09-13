<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\BonusSettingsResource\Pages;
use App\Filament\Resources\Manager\BonusSettingsResource\RelationManagers;
use App\Models\BonusSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BonusSettingsResource extends Resource
{
    protected static ?string $model = BonusSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Бонусная система';
    protected static ?string $pluralLabel = 'Настройки бонусов';
    protected static ?string $modelLabel = 'Настройка бонусов';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Начисления бонусов')
                    ->schema([
                        Forms\Components\TextInput::make('percent_per_order')
                            ->label('Процент от заказа (%)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Процент от суммы заказа, который начисляется как бонусы'),

                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Минимальная сумма заказа (₽)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('Минимальная сумма заказа для начисления бонусов'),

                        Forms\Components\TextInput::make('max_bonus_per_order')
                            ->label('Максимальный бонус за заказ')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Максимальное количество бонусов, которое можно начислить за один заказ'),

                        Forms\Components\TextInput::make('first_order_bonus')
                            ->label('Бонус за первый заказ')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Дополнительный бонус за первый заказ клиента'),

                        Forms\Components\TextInput::make('birthday_bonus')
                            ->label('Бонус на день рождения')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Бонус, который начисляется клиенту в день рождения'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Использование бонусов')
                    ->schema([
                        Forms\Components\TextInput::make('min_order_sum_for_spending')
                            ->label('Минимальная сумма для трат (₽)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->helperText('Минимальная сумма заказа для возможности потратить бонусы'),

                        Forms\Components\TextInput::make('rate')
                            ->label('Курс конвертации (₽ за бонус)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0.01)
                            ->helperText('Сколько рублей стоит один бонус'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Срок действия')
                    ->schema([
                        Forms\Components\TextInput::make('expire_days')
                            ->label('Срок действия бонусов (дней)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Через сколько дней бонусы становятся недействительными'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(BonusSettings::query())
            ->columns([
                Tables\Columns\TextColumn::make('percent_per_order')
                    ->label('Процент от заказа (%)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Мин. сумма заказа (₽)')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_bonus_per_order')
                    ->label('Макс. бонус за заказ')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate')
                    ->label('Курс (₽ за бонус)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expire_days')
                    ->label('Срок действия (дней)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Настройки бонусов не найдены')
            ->emptyStateDescription('Настройки будут созданы автоматически при первом обращении');
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
            'index' => Pages\ListBonusSettings::route('/'),
            'edit' => Pages\EditBonusSettings::route('/edit/{record}'),
        ];
    }
}
