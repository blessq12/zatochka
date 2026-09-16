<?php

namespace App\Http\Middleware;

use App\Infrastructure\Identity\Model\ManagerModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ManagerModel) {
            return response()->json(['message' => 'Forbidden. Manager role required.'], 403);
        }

        return $next($request);
    }
}
