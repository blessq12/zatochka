<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RefreshDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Переподключаемся к БД для избежания prepared statement ошибок на shared hosting
        try {
            DB::disconnect();
            DB::reconnect();
        } catch (\Exception $e) {
            // Игнорируем ошибки переподключения
        }

        return $next($request);
    }
}
