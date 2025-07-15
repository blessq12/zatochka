<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    // protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 8;
    protected static ?string $title = 'Настройки';

    protected static string $view = 'filament.pages.settings';

    public ?array $state = [];

    public function mount(): void
    {
        $this->form->fill([
            'telegram_bot_token' => config('services.telegram.bot_token'),
            'telegram_webhook_url' => config('services.telegram.webhook_url'),
            'discount_percent' => config('services.discounts.default_percent', 5),
            'bonus_points_rate' => config('services.bonuses.points_rate', 1),
            'min_order_amount_for_bonus' => config('services.bonuses.min_order_amount', 1000),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Telegram API')
                    ->schema([
                        Forms\Components\TextInput::make('telegram_bot_token')
                            ->label('Токен бота')
                            ->required()
                            ->helperText('Токен, полученный от @BotFather'),
                        Forms\Components\TextInput::make('telegram_webhook_url')
                            ->label('URL для вебхука')
                            ->required()
                            ->helperText('URL, на который Telegram будет отправлять обновления'),
                    ])->columns(2),

                Forms\Components\Section::make('Система скидок')
                    ->schema([
                        Forms\Components\TextInput::make('discount_percent')
                            ->label('Процент скидки по умолчанию')
                            ->numeric()
                            ->required()
                            ->default(5)
                            ->helperText('Базовый процент скидки для всех клиентов'),
                    ]),

                Forms\Components\Section::make('Бонусная система')
                    ->schema([
                        Forms\Components\TextInput::make('bonus_points_rate')
                            ->label('Курс бонусных баллов')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->helperText('Сколько рублей = 1 бонусный балл'),
                        Forms\Components\TextInput::make('min_order_amount_for_bonus')
                            ->label('Минимальная сумма заказа для начисления баллов')
                            ->numeric()
                            ->required()
                            ->default(1000)
                            ->helperText('От какой суммы заказа начисляются бонусные баллы'),
                    ])->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Здесь должна быть логика сохранения настроек
        // Например, через config:set или в базу данных

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
