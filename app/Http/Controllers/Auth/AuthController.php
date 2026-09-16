<?php

namespace App\Http\Controllers\Auth;

use App\Application\Identity\Command\LoginManagerCommand;
use App\Application\Identity\Command\LoginManagerHandler;
use App\Application\Identity\Command\LoginMasterCommand;
use App\Application\Identity\Command\LoginMasterHandler;
use App\Http\Controllers\Controller;
use App\Infrastructure\Identity\Model\ManagerModel;
use App\Infrastructure\Identity\Model\MasterModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(
        private LoginMasterHandler $loginMaster,
        private LoginManagerHandler $loginManager,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $this->loginMaster->handle(new LoginMasterCommand(
                $data['email'],
                $data['password'],
            ));
        } catch (\App\Shared\Domain\DomainException $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }

        return response()->json($result);
    }

    public function managerLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $this->loginManager->handle(new LoginManagerCommand(
                $data['email'],
                $data['password'],
            ));
        } catch (\App\Shared\Domain\DomainException $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }

        return response()->json($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user instanceof ManagerModel) {
            return $this->ok([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'manager',
            ]);
        }

        if ($user instanceof MasterModel) {
            return $this->ok([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'master',
            ]);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
