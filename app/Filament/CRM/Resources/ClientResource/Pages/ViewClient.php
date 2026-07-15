<?php

namespace App\Filament\CRM\Resources\ClientResource\Pages;

use App\Application\CRM\Command\AccrueBonusCommand;
use App\Application\CRM\Command\AccrueBonusHandler;
use App\Filament\CRM\Resources\ClientResource;
use App\Infrastructure\CRM\Model\ClientModel;
use App\Shared\Domain\DomainException;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Клиент';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            Action::make('accrueBonus')
                ->label('Начислить бонусы')
                ->icon(Heroicon::OutlinedBanknotes)
                ->form([
                    TextInput::make('amount')
                        ->label('Сумма')
                        ->numeric()
                        ->required()
                        ->gt(0),
                ])
                ->action(function (array $data): void {
                    try {
                        /** @var ClientModel $record */
                        $record = $this->getRecord();
                        app(AccrueBonusHandler::class)->handle(new AccrueBonusCommand(
                            (int) $record->id,
                            (string) $data['amount'],
                        ));
                        $this->getRecord()->refresh();
                        Notification::make()->title('Бонусы начислены')->success()->send();
                    } catch (DomainException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
