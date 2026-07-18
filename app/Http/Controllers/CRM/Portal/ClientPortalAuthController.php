<?php

namespace App\Http\Controllers\CRM\Portal;

use App\Application\CRM\Command\LoginClientPortalCommand;
use App\Application\CRM\Command\LoginClientPortalHandler;
use App\Application\CRM\Command\RegisterClientPortalCommand;
use App\Application\CRM\Command\RegisterClientPortalHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientPortalAuthController extends Controller
{
    public function __construct(
        private RegisterClientPortalHandler $register,
        private LoginClientPortalHandler $login,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $result = $this->register->handle(new RegisterClientPortalCommand(
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['password'],
        ));

        return response()->json([
            'token' => $result['token'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->login->handle(new LoginClientPortalCommand(
            $data['phone'],
            $data['password'],
        ));

        return response()->json([
            'token' => $result['token'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
