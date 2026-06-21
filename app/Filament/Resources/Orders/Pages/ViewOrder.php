<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Filament\Resources\Orders\Actions\OrderManageActions;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\OrderViewPresenter;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function resolveRecord(int | string $key): Model
    {
        return static::getResource()::getEloquentQuery()
            ->with(['works', 'materials', 'tools'])
            ->findOrFail($key);
    }

    public function getTitle(): string | Htmlable
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();

        return $record->order_number;
    }

    public function getSubheading(): string | Htmlable | null
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();

        return sprintf(
            '%s · %s',
            OrderViewPresenter::clientDisplayName($record),
            $record->status->label(),
        );
    }

    public function refreshOrderRecord(): void
    {
        $this->record = $this->resolveRecord($this->getRecord()->getKey());
    }

    protected function getHeaderActions(): array
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();

        $documents = ActionGroup::make([
            OrderManageActions::printReceipt(),
            OrderManageActions::printHandoverAct(),
        ])
            ->label('Документы')
            ->icon('heroicon-o-document-text')
            ->button();

        return match ($record->status) {
            OrderStatus::New => [
                OrderManageActions::assignMaster(),
                OrderManageActions::cancel(),
                $documents,
            ],
            OrderStatus::InWork, OrderStatus::WaitingParts => [
                OrderManageActions::linkEquipment(),
                $documents,
            ],
            OrderStatus::Ready => [
                OrderManageActions::issue(),
                OrderManageActions::returnForRework(),
                $documents,
            ],
            default => [
                $documents,
            ],
        };
    }
}
