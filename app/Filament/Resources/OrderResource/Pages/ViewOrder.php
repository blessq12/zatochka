<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\Document\DocumentGenerationService;
use App\Services\Document\Factories\DocumentGeneratorFactory;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Генерация акта приема доступна только для новых заказов
        $canGenerateAcceptance = in_array($this->record->status, [
            Order::STATUS_NEW,
            Order::STATUS_CONSULTATION,
            Order::STATUS_DIAGNOSTIC,
        ]);

        // Генерация акта выдачи доступна только для завершенных заказов
        $canGenerateIssuance = in_array($this->record->status, [
            Order::STATUS_READY,
            Order::STATUS_ISSUED,
        ]);

        if ($canGenerateAcceptance || $canGenerateIssuance) {
            $documentActions = [];

            if ($canGenerateAcceptance) {
                $documentActions[] = Actions\Action::make('generate_acceptance')
                    ->label('Сгенерировать акт приема')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn () => url('/api/orders/' . $this->record->id . '/documents/view?type=' . DocumentGeneratorFactory::TYPE_ACCEPTANCE))
                    ->openUrlInNewTab();
            }

            if ($canGenerateIssuance) {
                $documentActions[] = Actions\Action::make('generate_issuance')
                    ->label('Сгенерировать акт выдачи')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->url(fn () => url('/api/orders/' . $this->record->id . '/documents/view?type=' . DocumentGeneratorFactory::TYPE_ISSUANCE))
                    ->openUrlInNewTab();
            }

            if (!empty($documentActions)) {
                $actions[] = Actions\ActionGroup::make($documentActions)
                    ->label('Документы')
                    ->icon('heroicon-o-document-text')
                    ->button();
            }
        }

        $actions[] = Actions\EditAction::make();

        return $actions;
    }
}
