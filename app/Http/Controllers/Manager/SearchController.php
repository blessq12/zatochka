<?php

namespace App\Http\Controllers\Manager;

use App\Application\Crm\Query\GlobalSearchHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    public function __construct(
        private GlobalSearchHandler $search,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $q = is_string($q) ? $q : '';

        return response()->json($this->search->handle($q));
    }
}
