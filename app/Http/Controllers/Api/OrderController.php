<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\ApiUseCases\CreateClientOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        try {
            $useCase = app(CreateClientOrder::class);
            $result = $useCase->loadData($request->all())->validate()->execute();
            \Illuminate\Support\Facades\Log::info($result);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
