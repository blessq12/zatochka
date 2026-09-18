<?php

namespace App\Http\Controllers;

use App\Application\Crm\Query\GetActorHandler;
use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Domain\Crm\ActorType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Composition root: Identity registration + CRM actor read.
 * Does not live inside either BC Application layer.
 */
final class ProvisionActorController extends Controller
{
    public function __construct(
        private RegisterActorWithIdentityHandler $register,
        private GetActorHandler $getActor,
    ) {}

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'birthday' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->register->handle(
            $type,
            $data['email'],
            $data['password'],
            issueToken: false,
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            birthday: isset($data['birthday']) ? (string) $data['birthday'] : null,
            deliveryAddress: $data['delivery_address'] ?? null,
        );

        $actorType = ActorType::fromRoute($type);
        $actor = $this->getActor->handle($actorType, $result->actor->id);

        return response()->json($actor?->toArray() ?? ['id' => $result->actor->id], 201);
    }
}
