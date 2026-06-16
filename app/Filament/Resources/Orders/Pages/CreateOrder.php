<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Application\OrderFulfillment\Command\CreateOrderCommand;
use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Filament\Resources\Orders\OrderResource;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $clientId = $data['client_id'] ?? null;
        $snapshot = null;

        if ($clientId === null) {
            $snapshot = new ClientSnapshot([
                'full_name' => $data['client_full_name'] ?? '',
                'phone' => $data['client_phone'] ?? '',
            ]);
        }

        $order = app(CreateOrderHandler::class)->handle(new CreateOrderCommand(
            serviceTypes: $data['service_types'],
            clientId: $clientId,
            clientSnapshot: $snapshot,
            urgency: isset($data['urgency']) ? OrderUrgency::from($data['urgency']) : null,
            needsDelivery: (bool) ($data['needs_delivery'] ?? false),
            deliveryAddress: $data['delivery_address'] ?? null,
            problemDescription: $data['problem_description'] ?? null,
        ));

        return OrderModel::query()->findOrFail($order->id());
    }
}
