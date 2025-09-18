<?php

namespace App\Http\Controllers\Api;

//use cases
use App\Application\UseCases\Bonus\GetClientBonusAccount;
use App\Application\UseCases\Client\GetClientUseCase;
use App\Application\UseCases\ApiUseCases\GetClientOrderUseCase;
use App\Application\UseCases\Client\UpdateClientUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function clientUpdate(Request $request)
    {
        try {
            $client = auth('sanctum')->user();
            $useCase = app(UpdateClientUseCase::class);
            $client = $useCase->loadData($request->all())->validate()->execute();
            return response()->json([
                'client' => $client->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
