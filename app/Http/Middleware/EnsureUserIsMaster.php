<?php

namespace App\Http\Middleware;

use App\Infrastructure\Identity\Model\MasterAccountModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsMaster
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof MasterAccountModel) {
            return response()->json(['message' => 'Forbidden. Master role required.'], 403);
        }

        return $next($request);
    }
}
