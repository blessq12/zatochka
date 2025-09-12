<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//use cases
use App\Application\UseCases\Order\CreateOrderUseCase;
use App\Application\UseCases\Order\GetOrderUseCase;
use App\Application\UseCases\Order\UpdateOrderUseCase;
use App\Application\UseCases\Order\DeleteOrderUseCase;

class TestController extends Controller
{
    public function createOrder(Request $request)
    {
        return (new CreateOrderUseCase())->loadData($request->all())->validate()->execute();
    }

    public function getOrder(int $id)
    {
        return (new GetOrderUseCase())->loadData(['id' => $id])->validate()->execute();
    }

    public function updateOrder(int $id, Request $request)
    {
        return (new UpdateOrderUseCase())->loadData(['id' => $id, ...$request->all()])->validate()->execute();
    }

    public function deleteOrder(int $id)
    {
        return (new DeleteOrderUseCase())->loadData(['id' => $id])->validate()->execute();
    }
}
