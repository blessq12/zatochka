<?php

namespace App\Http\Controllers\Identity;

use App\Application\Identity\Command\LoginIdentityHandler;
use App\Application\Identity\Command\LogoutIdentityHandler;
use App\Application\Identity\Command\RegisterActorWithIdentityHandler;
use App\Application\Identity\Query\GetMeHandler;
use App\Http\Controllers\Controller;
use App\Infrastructure\Identity\Eloquent\IdentityModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class IdentityController extends Controller
{
    public function __construct(
        private RegisterActorWithIdentityHandler $register,
        private LoginIdentityHandler $login,
        private LogoutIdentityHandler $logout,
        private GetMeHandler $me,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'actor_type' => ['required', 'string', 'in:clients'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'birthday' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->register->handle(
            $data['actor_type'],
            $data['email'],
            $data['password'],
            issueToken: true,
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            birthday: isset($data['birthday']) ? (string) $data['birthday'] : null,
            deliveryAddress: $data['delivery_address'] ?? null,
        );

        return response()->json($result->toArray(), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'expected_actor_type' => ['sometimes', 'nullable', 'string', 'in:clients,managers,masters'],
        ]);

        $result = $this->login->handle(
            $data['email'],
            $data['password'],
            $data['expected_actor_type'] ?? null,
        );

        return response()->json($result->toArray());
    }

    public function logout(Request $request): Response
    {
        $this->logout->handle($request);

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        /** @var IdentityModel $identity */
        $identity = $request->user();
        $result = $this->me->handle((int) $identity->id, (string) $identity->email);

        return response()->json($result->toArray());
    }
}
