<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientTelegramVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user();

        if (!$client || !$client->isTelegramVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Необходима верификация Telegram аккаунта',
                'data' => [
                    'requires_verification' => true,
                    'telegram' => $client?->telegram,
                ]
            ], 403);
        }

        return $next($request);
    }
}
