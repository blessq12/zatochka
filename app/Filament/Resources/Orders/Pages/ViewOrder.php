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

    /** @var list<string> */
    private const ORDER_RELATIONS = ['works', 'materials', 'tools'];

    public function hydrate(): void
    {
        parent::hydrate();

        $this->reloadOrderRelations();
    }

    public function resolveRecord(int|string $key): Model
    {
        return static::getResource()::getEloquentQuery()
            ->with(self::ORDER_RELATIONS)
            ->findOrFail($key);
    }

    public function getTitle(): string|Htmlable
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();

        return $record->order_number;
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var OrderModel $record */
        $record = $this->getRecord();

        return sprintf(
            '%s · %s · %s',
            OrderViewPresenter::clientDisplayName($record),
            $record->status->label(),
            OrderViewPresenter::financialSummaryLabel($record),
        );
    }

    public function refreshOrderRecord(): void
    {
        $this->record = $this->resolveRecord($this->getRecord()->getKey());

        $this->cacheSchema('infolist', null);
        $this->cacheSchema('content', null);
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

    private function reloadOrderRelations(): void
    {
        if (! $this->record instanceof OrderModel) {
            return;
        }

        foreach (self::ORDER_RELATIONS as $relation) {
            $this->record->unsetRelation($relation);
        }

        $this->record->refresh();
        $this->record->load(self::ORDER_RELATIONS);
    }
}
