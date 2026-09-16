<?php

namespace App\Http\Middleware;

use App\Infrastructure\Identity\Model\ManagerModel;
use App\Infrastructure\Identity\Model\MasterModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Manager or Master — shared read/write surfaces (stock, equipment). */
final class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ManagerModel && ! $user instanceof MasterModel) {
            return response()->json(['message' => 'Forbidden. Staff role required.'], 403);
        }

        return $next($request);
    }
}
