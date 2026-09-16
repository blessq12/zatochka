<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Query\ListPublishedReviewsHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublishedReviewsController extends Controller
{
    public function __construct(
        private ListPublishedReviewsHandler $listPublishedReviews,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $limit = $request->query('limit');
        $resolvedLimit = is_numeric($limit) ? max(1, min(50, (int) $limit)) : null;

        return $this->ok($this->listPublishedReviews->handle($resolvedLimit));
    }
}
