<?php

namespace App\Http\Middleware;

use App\Shared\Domain\DomainException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConvertDomainException
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
