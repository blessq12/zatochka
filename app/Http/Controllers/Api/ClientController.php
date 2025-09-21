<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function clientOrdersGet()
    {

        $client = auth('sanctum')->user();
        $orders = $client->orders;

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function clientSelf()
    {
        $client = auth('sanctum')->user();
        $bonusAccount = $client->bonusAccount;

        return response()->json([
            'client' => $client,
            'bonusAccount' => $bonusAccount,
        ]);
    }

    public function clientUpdate(Request $request)
    {
        try {
            $client = auth('sanctum')->user();

            $client = \App\Models\Client::find($client->id)->update(
                $request->all()
            );

            return response()->json([
                'client' => $client,
                'message' => 'Client updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
