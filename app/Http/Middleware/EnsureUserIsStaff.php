<?php

namespace App\Http\Middleware;

use App\Infrastructure\Identity\Model\ManagerAccountModel;
use App\Infrastructure\Identity\Model\MasterAccountModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Manager or Master — shared read/write surfaces (stock, equipment). */
final class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof ManagerAccountModel && ! $user instanceof MasterAccountModel) {
            return response()->json(['message' => 'Forbidden. Staff role required.'], 403);
        }

        return $next($request);
    }
}
