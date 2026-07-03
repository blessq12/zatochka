<?php

namespace App\Http\Controllers\Api;

use App\Application\ClientPortal\Command\SubmitReviewCommand;
use App\Application\ClientPortal\Command\UpdateClientProfileCommand;
use App\Application\ClientPortal\CommandHandler\SubmitReviewHandler;
use App\Application\ClientPortal\CommandHandler\UpdateClientProfileHandler;
use App\Application\ClientPortal\Presenter\ClientOrderPresenter;
use App\Application\ClientPortal\Presenter\ClientProfilePresenter;
use App\Application\ClientPortal\Presenter\ReviewPresenter;
use App\Application\ClientPortal\Query\GetClientOrderDetailQuery;
use App\Application\ClientPortal\Query\GetClientOrdersQuery;
use App\Application\ClientPortal\Query\GetClientProfileQuery;
use App\Application\ClientPortal\QueryHandler\GetClientOrderDetailQueryHandler;
use App\Application\ClientPortal\QueryHandler\GetClientOrdersQueryHandler;
use App\Application\ClientPortal\QueryHandler\GetClientProfileQueryHandler;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;
use App\Infrastructure\ClientPortal\Auth\ClientAuthModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientAccountController
{
    public function profile(Request $request, GetClientProfileQueryHandler $handler): JsonResponse
    {
        $client = $handler->handle(new GetClientProfileQuery($this->clientId($request)));

        return response()->json(['data' => ClientProfilePresenter::present($client)]);
    }

    public function updateProfile(Request $request, UpdateClientProfileHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $client = $handler->handle(new UpdateClientProfileCommand(
            clientId: $this->clientId($request),
            fullName: $validated['full_name'],
            email: $validated['email'] ?? null,
            birthDate: $validated['birth_date'] ?? null,
            deliveryAddress: $validated['delivery_address'] ?? null,
        ));

        return response()->json(['data' => ClientProfilePresenter::present($client)]);
    }

    public function activeOrders(Request $request, GetClientOrdersQueryHandler $handler): JsonResponse
    {
        return $this->ordersResponse($request, $handler, history: false);
    }

    public function orderHistory(Request $request, GetClientOrdersQueryHandler $handler): JsonResponse
    {
        return $this->ordersResponse($request, $handler, history: true);
    }

    public function orderDetail(int $orderId, Request $request, GetClientOrderDetailQueryHandler $handler, ReviewRepositoryInterface $reviews): JsonResponse
    {
        $clientId = $this->clientId($request);
        $order = $handler->handle(new GetClientOrderDetailQuery($clientId, $orderId));
        $reviewExists = $reviews->findByOrderId($orderId) !== null;

        return response()->json([
            'data' => ClientOrderPresenter::detail($order, $reviewExists),
        ]);
    }

    public function submitReview(int $orderId, Request $request, SubmitReviewHandler $handler): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = $handler->handle(new SubmitReviewCommand(
            clientId: $this->clientId($request),
            orderId: $orderId,
            rating: (int) $validated['rating'],
            comment: $validated['comment'] ?? null,
        ));

        return response()->json(['data' => ReviewPresenter::present($review)], 201);
    }

    private function ordersResponse(Request $request, GetClientOrdersQueryHandler $handler, bool $history): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $result = $handler->handle(new GetClientOrdersQuery(
            clientId: $this->clientId($request),
            history: $history,
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? 20),
        ));

        return response()->json([
            'data' => ClientOrderPresenter::list($result['items'], $result['review_order_ids']),
            'meta' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
            ],
        ]);
    }

    private function clientId(Request $request): int
    {
        $user = $request->user();

        if (! $user instanceof ClientAuthModel) {
            abort(401, 'Требуется авторизация клиента.');
        }

        return $user->id;
    }
}
