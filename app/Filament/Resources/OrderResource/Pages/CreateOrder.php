<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
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
            $data['manager_id'] = \Illuminate\Support\Facades\Auth::id();
        }

        return $data;
    }
}
