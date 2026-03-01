<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $client = \App\Models\Client::where('phone', $request->phone)->first();

        if (! $client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $passwordValid = $client->password && Hash::check($request->password, $client->password);
        $temporaryValid = $client->temporary_password
            && ! $client->temporary_password_used
            && Hash::check($request->password, $client->temporary_password);

        if (! $passwordValid && ! $temporaryValid) {
            return response()->json(['message' => 'Invalid password'], 401);
        }

        $token = $client->createToken('client_auth_token')->plainTextToken;
        $response = [
            'message' => 'Login successful',
            'token' => $token,
            'client' => $client,
        ];
        if ($temporaryValid && ! $passwordValid) {
            $response['requires_password_set'] = true;
        }

        return response()->json($response);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'phone' => 'required|unique:clients,phone',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        try {
            $clientData = $request->all();
            $clientData['password'] = Hash::make($request->password);

            $client = \App\Models\Client::create($clientData);
            // Создаем токен через Sanctum с уникальным именем для клиента
        $token = $client->createToken('client_auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registration successful',
                'token' => $token,
                'client' => $client
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
