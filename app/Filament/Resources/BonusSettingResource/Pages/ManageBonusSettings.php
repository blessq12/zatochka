<?php

namespace App\Filament\Resources\BonusSettingResource\Pages;

use App\Filament\Resources\BonusSettingResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use App\Models\BonusSetting;
use Filament\Notifications\Notification;

class ManageBonusSettings extends Page
{
    protected static string $resource = BonusSettingResource::class;

    protected static string $view = 'filament.resources.bonus-setting-resource.pages.manage-bonus-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = BonusSetting::getConfig();
    }

    public function form(Form $form): Form
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
                                    ->required(),

                                TextInput::make('bonus_exchange_rate')
                                    ->label('Курс обмена бонусов')
                                    ->numeric()
                                    ->suffix('рублей за 1 бонус')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('bonus_expiration_months')
                                    ->label('Срок действия бонусов')
                                    ->numeric()
                                    ->suffix('месяцев')
                                    ->required(),

                                TextInput::make('birthday_bonus_amount')
                                    ->label('Бонусы за день рождения')
                                    ->numeric()
                                    ->suffix('бонусов')
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
                                    ->required(),

                                TextInput::make('min_order_amount_for_spend')
                                    ->label('Минимальная сумма заказа для списания')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_bonus_spend_percent')
                                    ->label('Максимальный процент списания от заказа')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required(),

                                TextInput::make('first_review_bonus_amount')
                                    ->label('Бонусы за первый отзыв')
                                    ->numeric()
                                    ->suffix('бонусов')
                                    ->required(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить настройки')
                ->action('save')
                ->color('success'),
        ];
    }

    public function save(): void
    {
        BonusSetting::updateConfig($this->data);

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
