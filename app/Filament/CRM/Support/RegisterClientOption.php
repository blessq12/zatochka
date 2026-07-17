<?php

namespace App\Filament\CRM\Support;

use App\Application\CRM\Command\RegisterClientCommand;
use App\Application\CRM\Command\RegisterClientHandler;
use App\Application\Shared\EntityIdGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Регистрация клиента «на лету» из чужих форм (createOption у Select).
 * UI-владение остаётся за CRM: форма и команда живут здесь.
 */
final class RegisterClientOption
{
    public static function applyTo(Select $select): Select
    {
        return $select
            ->createOptionForm(self::form())
            ->createOptionUsing(fn (array $data): int => self::register($data))
            ->createOptionAction(fn (Action $action): Action => $action
                ->label('Новый клиент')
                ->modalHeading('Новый клиент')
                ->modalSubmitActionLabel('Зарегистрировать')
                ->button()
                ->outlined()
                ->color('primary')
                ->icon(null));
    }

    /** @return list<TextInput> */
    public static function form(): array
    {
        return [
            TextInput::make('name')
                ->label('ФИО')
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->label('Телефон')
                ->tel()
                ->telRegex('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                ->mask('+7 (999) 999-99-99')
                ->placeholder('+7 (___) ___-__-__')
                ->required(),
            TextInput::make('email')
                ->label('Эл. почта')
                ->email()
                ->maxLength(255),
        ];
    }

    /** @param array<string, mixed> $data */
    public static function register(array $data): int
    {
        $ids = app(EntityIdGenerator::class);
        $clientId = $ids->next('client')->value;

        app(RegisterClientHandler::class)->handle(new RegisterClientCommand(
            $clientId,
            $ids->next('bonus_account')->value,
            $data['phone'],
            $data['name'],
            filled($data['email'] ?? null) ? $data['email'] : null,
        ));

        return $clientId;
    }
}
