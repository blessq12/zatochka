<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\LeadToOrderFormData;
use App\Filament\Support\OrderFormCommandBuilder;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public ?int $sourceLeadId = null;

    public function mount(): void
    {
        parent::mount();

        $leadId = request()->integer('lead');

        if ($leadId > 0) {
            $lead = SiteLeadModel::query()
                ->whereKey($leadId)
                ->where('converted', false)
                ->firstOrFail();

            $this->sourceLeadId = $lead->id;

            $this->form->fill(LeadToOrderFormData::fromLead($lead, auth()->id()));

            return;
        }

        $managerId = auth()->id();

        if ($managerId !== null) {
            $this->form->fill(['manager_id' => $managerId]);
        }
    }

    public function getSubheading(): ?string
    {
        if ($this->sourceLeadId === null) {
            return null;
        }

        return 'Из лида с сайта — проверьте и дополните данные перед сохранением';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $command = OrderFormCommandBuilder::buildCommand($data);

        $order = app(CreateOrderHandler::class)->handle($command);

        $orderId = $order->id();

        if ($orderId === null) {
            throw new \RuntimeException('Не удалось создать заказ.');
        }

        return OrderModel::query()->findOrFail($orderId);
    }

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
