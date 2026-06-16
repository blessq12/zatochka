<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\Query\GetPublicBootstrapQuery;
use App\Application\Catalog\QueryHandler\GetPublicBootstrapQueryHandler;
use Illuminate\Http\JsonResponse;

final class BootstrapController
{
    public function show(GetPublicBootstrapQueryHandler $handler): JsonResponse
    {
        $data = $handler->handle(new GetPublicBootstrapQuery);

        return response()->json(['data' => $data]);
    }
}
