<?php

namespace App\Http\Controllers\Api;

//use cases
use App\Application\UseCases\Bonus\GetClientBonusAccount;
use App\Application\UseCases\Client\GetClientUseCase;
use App\Application\UseCases\ApiUseCases\GetClientOrderUseCase;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    public function clientOrdersGet()
    {


        $client = auth('sanctum')->user();
        $useCase = app(GetClientOrderUseCase::class);
        $orders = $useCase->loadData(['id' => $client->id])->validate()->execute();
        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function clientSelf()
    {
        $client = auth('sanctum')->user();

        $getClientUseCase = app(GetClientUseCase::class);
        $getClientBonusAccountUseCase = app(GetClientBonusAccount::class);
        $client = $getClientUseCase->loadData(['id' => $client->id])->validate()->execute();
        $bonusAccount = $getClientBonusAccountUseCase->loadData(['id' => $client->id])->validate()->execute();


        return response()->json([
            'client' => $client->toArray(),
            'bonusAccount' => $bonusAccount->toArray(),
        ]);
    }
}
