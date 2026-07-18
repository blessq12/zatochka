<?php

namespace App\Filament\CRM\Resources\ClientResource\Actions;

use App\Application\CRM\Command\AccrueBonusCommand;
use App\Application\CRM\Command\AccrueBonusHandler;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class ClientMutationActions
{
    /** @return list<Action> */
    public static function all(): array
    {
        return [
            self::accrueBonus(),
        ];
    }

    public static function accrueBonus(): Action
    {
        return Action::make('accrueBonus')
            ->label('Начислить бонусы')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('success')
            ->form([
                TextInput::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->required()
                    ->gt(0),
            ])
            ->modalHeading('Начислить бонусы')
            ->modalSubmitActionLabel('Начислить')
            ->action(function (ClientModel $record, array $data): void {
                try {
                    app(AccrueBonusHandler::class)->handle(new AccrueBonusCommand(
                        (int) $record->id,
                        (string) $data['amount'],
                    ));
                    Notification::make()->title('Бонусы начислены')->success()->send();
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                }
            });
    }
}
