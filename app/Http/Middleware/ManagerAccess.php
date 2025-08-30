<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->checkAccess($request);
        return $next($request);
    }

    private function checkAccess(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'manager') {
            abort(403, 'У вас нет доступа к панели менеджера');
        }
    }
}
