<?php

namespace App\Domain\OrderFulfillment\Services;

use App\Domain\OrderFulfillment\Models\Order;
use Illuminate\Support\Str;

class OrderNumberGenerator
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $lastNumber = Order::query()
            ->where('order_number', 'like', "ORD-{$year}-%")
            ->orderByDesc('id')
            ->value('order_number');

        $sequence = 1;

        if ($lastNumber !== null && preg_match('/ORD-\d{4}-(\d+)/', $lastNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('ORD-%s-%s', $year, Str::padLeft((string) $sequence, 4, '0'));
    }
}
