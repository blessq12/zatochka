<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->role !== UserRole::Manager) {
            return response()->json(['message' => 'Forbidden. Manager role required.'], 403);
        }

        return $next($request);
    }
}
