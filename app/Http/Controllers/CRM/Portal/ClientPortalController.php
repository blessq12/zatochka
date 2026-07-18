<?php

namespace App\Http\Controllers\CRM\Portal;

use App\Application\CRM\Command\ChangeClientPortalPasswordCommand;
use App\Application\CRM\Command\ChangeClientPortalPasswordHandler;
use App\Application\CRM\Command\UpdateClientCommand;
use App\Application\CRM\Command\UpdateClientHandler;
use App\Application\CRM\Query\GetClientPortalProfileHandler;
use App\Application\CRM\Query\ListClientPortalOrdersHandler;
use App\Application\Feedback\Command\SubmitReviewCommand;
use App\Application\Feedback\Command\SubmitReviewHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientPortalController extends Controller
{
    public function __construct(
        private GetClientPortalProfileHandler $getProfile,
        private UpdateClientHandler $updateClient,
        private ChangeClientPortalPasswordHandler $changePassword,
        private ListClientPortalOrdersHandler $listOrders,
        private SubmitReviewHandler $submitReview,
        private EntityIdGenerator $ids,
    ) {}

    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $this->getProfile->handle((int) $user->client_id, $user);

        if ($profile === null) {
            return response()->json(['message' => 'Client not found.'], 404);
        }

        return $this->ok($profile);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'full_name' => ['nullable', 'string', 'min:2'],
            'email' => ['nullable', 'email'],
            'birth_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string'],
        ]);

        $this->updateClient->handle(new UpdateClientCommand(
            clientId: (int) $user->client_id,
            name: $data['full_name'] ?? null,
            email: $data['email'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            updateBirthDate: array_key_exists('birth_date', $data),
            updateDeliveryAddress: array_key_exists('delivery_address', $data),
        ));

        if (isset($data['full_name'])) {
            $user->name = $data['full_name'];
            $user->save();
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->email = $data['email'];
            $user->save();
        }

        $user->refresh();

        return $this->ok($this->getProfile->handle((int) $user->client_id, $user));
    }

    public function setPassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $this->changePassword->handle(new ChangeClientPortalPasswordCommand(
            (int) $user->id,
            $data['password'],
        ));

        $user->refresh();

        return $this->ok($this->getProfile->handle((int) $user->client_id, $user));
    }

    public function activeOrders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);

        $result = $this->listOrders->handle((int) $user->client_id, 'active', $page, $perPage);

        return response()->json([
            'data' => $this->serialize($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function historyOrders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);

        $result = $this->listOrders->handle((int) $user->client_id, 'history', $page, $perPage);

        return response()->json([
            'data' => $this->serialize($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function submitReview(Request $request, string $orderId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $this->submitReview->handle(new SubmitReviewCommand(
            $this->ids->next('review')->value,
            $orderId,
            (int) $user->client_id,
            (int) $data['rating'],
            $data['comment'] ?? null,
        ));

        return response()->json([
            'data' => [
                'status' => 'pending',
            ],
        ], 201);
    }
}
