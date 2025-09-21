<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $client = \App\Models\Client::where('phone', $request->client_phone)->first();

        if (! $client) {
            $client = \App\Models\Client::create([
                'full_name' => $request->client_name,
                'phone' => $request->client_phone,
            ]);
        }

        $order = $client->orders()->create([
            'type' => $request->service_type ?? Order::TYPE_REPAIR,
            'status' => Order::STATUS_NEW,
            'urgency' => Order::URGENCY_NORMAL,
            'client_id' => $client->id,
            'branch_id' => \App\Models\Branch::first()->id,
            ...$request->all(),
        ]);

        return response()->json([
            'order' => $order,
            'message' => 'Order created successfully',
        ], 200);
    }
}
