<?php

namespace App\Filament\Order\Resources\OrderResource\Pages;

use App\Domain\Order\VO\OrderStatus;
use App\Filament\Order\Resources\OrderResource;
use App\Infrastructure\Order\Model\OrderModel;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();
        $record->loadMissing('client');

        $status = OrderResource::statusOptions()[$record->status] ?? $record->status;
        $service = OrderResource::serviceTypeOptions()[$record->service_type] ?? $record->service_type;
        $client = $record->client;
        $clientLabel = $client === null
            ? 'Клиент #'.$record->client_id
            : trim(($client->name ?: 'Без имени').' · '.$client->phone);

        return $service.' · '.$status.' · '.$clientLabel;
    }

    protected function getHeaderActions(): array
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();

        $mutations = array_map(
            function ($action) {
                return $action->after(function (): void {
                    $this->getRecord()->refresh()->load(['client', 'items.equipment', 'warrantySourceOrder']);
                });
            },
            OrderResource::orderMutationActions(),
        );

        return [
            Action::make('backToList')
                ->label('К списку')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(OrderResource::getUrl('index')),
            ActionGroup::make($mutations)
                ->label('Статус заказа')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->iconPosition(IconPosition::After)
                ->button()
                ->color('primary')
                ->visible(fn (): bool => ! in_array(
                    $record->status,
                    [
                        OrderStatus::Cancelled->value,
                        OrderStatus::Closed->value,
                        OrderStatus::Issued->value,
                    ],
                    true,
                )),
        ];
    }
}
