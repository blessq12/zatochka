<?php

namespace App\Http\Controllers\Api;

use App\Application\PublicSite\Query\GetPublicBootstrapQuery;
use App\Application\PublicSite\QueryHandler\GetPublicBootstrapQueryHandler;
use Illuminate\Http\JsonResponse;

final class BootstrapController
{
    public function show(GetPublicBootstrapQueryHandler $handler): JsonResponse
    {
        $data = $handler->handle(new GetPublicBootstrapQuery);

        return response()->json(['data' => $data]);
    }
}
