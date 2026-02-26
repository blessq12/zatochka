<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Генерируем номер заказа в транзакции
        $data['order_number'] = DB::transaction(function () {
            return Order::generateOrderNumber();
        });

        // Устанавливаем первый филиал, если не указан
        if (empty($data['branch_id'])) {
            $data['branch_id'] = \App\Models\Branch::first()?->id;
        }

        // Устанавливаем текущего пользователя как менеджера, если не указан
        if (empty($data['manager_id'])) {
            $data['manager_id'] = Auth::id();
        }

        // Автоматически проставляем источник клиента в заказе по данным клиента
        if (!empty($data['client_id'])) {
            $client = Client::find($data['client_id']);

            if ($client && $client->marketing_source) {
                $data['client_source'] = $client->marketing_source;
            }
        }

        return $data;
    }
}

