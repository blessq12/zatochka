<?php

namespace App\Filament\Resources\Manager\BonusSettingsResource\Pages;

use App\Filament\Resources\Manager\BonusSettingsResource;
use App\Models\BonusSettings;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditBonusSettings extends EditRecord
{
    protected static string $resource = BonusSettingsResource::class;

    protected function resolveRecord(int | string $key): Model
    {
        return BonusSettings::getSettings();
    }

    public function mount(int | string $record = null): void
    {
        $this->record = BonusSettings::getSettings();
        $this->authorizeAccess();
        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reset_to_defaults')
                ->label('Сбросить к значениям по умолчанию')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $settings = BonusSettings::getSettings();
                    $settings->update([
                        'birthday_bonus' => 0,
                        'first_order_bonus' => 0,
                        'rate' => 1.00,
                        'percent_per_order' => 5.00,
                        'min_order_sum_for_spending' => 1000.00,
                        'expire_days' => 365,
                        'min_order_amount' => 100.00,
                        'max_bonus_per_order' => 1000,
                    ]);

                    Notification::make()
                        ->title('Настройки сброшены')
                        ->body('Все настройки бонусной системы сброшены к значениям по умолчанию')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Сброс настроек')
                ->modalDescription('Вы уверены, что хотите сбросить все настройки бонусной системы к значениям по умолчанию?')
                ->modalSubmitActionLabel('Да, сбросить')
                ->modalCancelActionLabel('Отмена'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Настройки сохранены')
            ->body('Настройки бонусной системы успешно обновлены')
            ->success()
            ->send();
    }
}
