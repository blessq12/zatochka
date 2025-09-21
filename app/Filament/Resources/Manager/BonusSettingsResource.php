<?php

namespace App\Filament\Resources\Manager;

use App\Filament\Resources\Manager\BonusSettingsResource\Pages;
use App\Models\BonusSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BonusSettingsResource extends Resource
{
    protected static ?string $model = BonusSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки бонусов';

    protected static ?string $modelLabel = 'Настройка бонусов';

    protected static ?string $pluralModelLabel = 'Настройки бонусов';

    protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основные настройки')
                    ->schema([
                        Forms\Components\TextInput::make('birthday_bonus')
                            ->label('Бонус на день рождения')
                            ->numeric()
                            ->suffix('бонусов')
                            ->helperText('Количество бонусов, начисляемых клиенту на день рождения'),

                        Forms\Components\TextInput::make('first_order_bonus')
                            ->label('Бонус за первый заказ')
                            ->numeric()
                            ->suffix('бонусов')
                            ->helperText('Количество бонусов, начисляемых за первый заказ'),

                        Forms\Components\TextInput::make('rate')
                            ->label('Курс конвертации')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('рублей за бонус')
                            ->helperText('Сколько рублей стоит 1 бонус'),

                        Forms\Components\TextInput::make('percent_per_order')
                            ->label('Процент начислений с заказа')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Какой процент от суммы заказа начисляется в виде бонусов'),

                        Forms\Components\TextInput::make('min_order_sum_for_spending')
                            ->label('Минимальная сумма для списания бонусов')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('₽')
                            ->helperText('Минимальная сумма заказа, при которой можно тратить бонусы'),

                        Forms\Components\TextInput::make('expire_days')
                            ->label('Срок действия бонусов')
                            ->numeric()
                            ->suffix('дней')
                            ->helperText('Через сколько дней бонусы сгорают'),

                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Минимальная сумма заказа для начисления')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('₽')
                            ->helperText('Минимальная сумма заказа для начисления бонусов'),

                        Forms\Components\TextInput::make('max_bonus_per_order')
                            ->label('Максимальные бонусы за заказ')
                            ->numeric()
                            ->suffix('бонусов')
                            ->helperText('Максимальное количество бонусов, которое можно получить за один заказ'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('birthday_bonus')
                    ->label('Бонус на день рождения')
                    ->numeric()
                    ->suffix(' бонусов'),

                Tables\Columns\TextColumn::make('first_order_bonus')
                    ->label('Бонус за первый заказ')
                    ->numeric()
                    ->suffix(' бонусов'),

                Tables\Columns\TextColumn::make('rate')
                    ->label('Курс конвертации')
                    ->money('RUB')
                    ->suffix(' за бонус'),

                Tables\Columns\TextColumn::make('percent_per_order')
                    ->label('Процент начислений')
                    ->numeric()
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('min_order_sum_for_spending')
                    ->label('Мин. сумма для списания')
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('expire_days')
                    ->label('Срок действия')
                    ->numeric()
                    ->suffix(' дней'),

                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Мин. сумма заказа')
                    ->money('RUB'),

                Tables\Columns\TextColumn::make('max_bonus_per_order')
                    ->label('Макс. бонусы за заказ')
                    ->numeric()
                    ->suffix(' бонусов'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset_to_defaults')
                    ->label('Сбросить к умолчаниям')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (BonusSettings $record): void {
                        $record->update([
                            'birthday_bonus' => 0,
                            'first_order_bonus' => 0,
                            'rate' => 1.00,
                            'percent_per_order' => 5.00,
                            'min_order_sum_for_spending' => 1000.00,
                            'expire_days' => 365,
                            'min_order_amount' => 100.00,
                            'max_bonus_per_order' => 1000,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Настройки сброшены к значениям по умолчанию')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Нет массовых действий для настроек
            ])
            ->defaultSort('updated_at', 'desc');
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
            'edit' => Pages\EditBonusSettings::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Нельзя создавать новые настройки, только редактировать существующие
    }
}
