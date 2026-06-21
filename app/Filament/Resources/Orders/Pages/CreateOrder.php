<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Application\OrderFulfillment\CommandHandler\CreateOrderHandler;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\OrderFormCommandBuilder;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

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
