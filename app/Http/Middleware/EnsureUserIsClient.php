<?php

namespace App\Http\Middleware;

use App\Infrastructure\CRM\Model\ClientModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ClientModel) {
            return response()->json(['message' => 'Forbidden. Client portal access required.'], 403);
        }

        return $next($request);
    }
}
