<?php

namespace App\Http\Controllers\Manager;

use App\Application\Manager\Query\GetManagerDashboardHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __construct(
        private GetManagerDashboardHandler $dashboard,
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json($this->dashboard->handle());
    }
}
