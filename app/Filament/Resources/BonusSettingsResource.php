<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusSettingsResource\Pages;
use App\Filament\Resources\BonusSettingsResource\RelationManagers;
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

    protected static ?string $navigationLabel = 'Настройки бонусов';

    protected static ?string $modelLabel = 'Настройки бонусной системы';

    protected static ?string $pluralModelLabel = 'Настройки бонусной системы';

    protected static ?string $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Начисления бонусов')
                    ->schema([
                        Forms\Components\TextInput::make('percent_per_order')
                            ->label('Процент начислений с заказа')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->default(5.00)
                            ->helperText('Процент от суммы заказа, который начисляется бонусами'),

                        Forms\Components\TextInput::make('min_order_amount')
                            ->label('Минимальная сумма заказа для начисления')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('₽')
                            ->default(100.00)
                            ->helperText('Заказы на меньшую сумму не дают бонусы'),

                        Forms\Components\TextInput::make('max_bonus_per_order')
                            ->label('Максимальные бонусы за один заказ')
                            ->required()
                            ->numeric()
                            ->default(1000)
                            ->helperText('Лимит бонусов, которые можно получить с одного заказа'),

                        Forms\Components\TextInput::make('first_order_bonus')
                            ->label('Бонус за первый заказ')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Бонусы, которые начисляются при первом заказе'),

                        Forms\Components\TextInput::make('birthday_bonus')
                            ->label('Бонус на день рождения')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Бонусы, которые начисляются в день рождения клиента'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Списание бонусов')
                    ->schema([
                        Forms\Components\TextInput::make('min_order_sum_for_spending')
                            ->label('Минимальная сумма заказа для списания')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('₽')
                            ->default(1000.00)
                            ->helperText('Минимальная сумма заказа, при которой можно использовать бонусы'),

                        Forms\Components\TextInput::make('rate')
                            ->label('Курс обмена')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->default(1.00)
                            ->suffix('₽ за 1 бонус')
                            ->helperText('Сколько рублей стоит 1 бонусный балл'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Срок действия')
                    ->schema([
                        Forms\Components\TextInput::make('expire_days')
                            ->label('Срок действия бонусов (дней)')
                            ->required()
                            ->numeric()
                            ->default(365)
                            ->suffix('дней')
                            ->helperText('Через сколько дней бонусы истекают'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('percent_per_order')
                    ->label('Процент начислений')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate')
                    ->label('Курс обмена')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('₽')
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_order_bonus')
                    ->label('Бонус за первый заказ')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('birthday_bonus')
                    ->label('Бонус на день рождения')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_order_amount')
                    ->label('Мин. сумма для начисления')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_order_sum_for_spending')
                    ->label('Мин. сумма для списания')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_bonus_per_order')
                    ->label('Макс. бонусов за заказ')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expire_days')
                    ->label('Срок действия')
                    ->numeric()
                    ->suffix(' дней')
                    ->sortable(),

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
            ])
            ->bulkActions([
                // Нет массовых действий для настроек
            ]);
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
            'index' => Pages\EditBonusSettings::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Всегда возвращаем единственную запись настроек или создаем её
        $settings = \App\Models\BonusSettings::getSettings();
        return parent::getEloquentQuery()->where('id', $settings->id);
    }

    public static function canCreate(): bool
    {
        // Нельзя создавать новые настройки - только редактировать существующие
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
