<?php

namespace App\Http\Controllers\Order;

use App\Application\Order\Command\CreatePublicOrderCommand;
use App\Application\Order\Command\CreatePublicOrderHandler;
use App\Domain\Order\VO\OrderServiceType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PublicOrderController extends Controller
{
    public function __construct(
        private CreatePublicOrderHandler $createPublicOrder,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:2'],
            'phone' => ['required', 'string'],
            'service_type' => ['required', 'string', Rule::enum(OrderServiceType::class)],
            'comment' => ['nullable', 'string'],
            'needs_delivery' => ['nullable', 'boolean'],
            'delivery_address' => ['nullable', 'string'],
            'intake_data' => ['nullable', 'array'],
        ]);

        $authenticatedClientId = null;
        $user = $request->user('sanctum');

        if ($user instanceof User && $user->role === UserRole::Client && $user->client_id !== null) {
            $authenticatedClientId = (int) $user->client_id;
        }

        $result = $this->createPublicOrder->handle(new CreatePublicOrderCommand(
            fullName: $data['full_name'],
            phone: $data['phone'],
            serviceType: $data['service_type'],
            needsDelivery: (bool) ($data['needs_delivery'] ?? false),
            deliveryAddress: $data['delivery_address'] ?? null,
            comment: $data['comment'] ?? null,
            intake: $data['intake_data'] ?? null,
            authenticatedClientId: $authenticatedClientId,
        ));

        return $this->created($result);
    }
}
