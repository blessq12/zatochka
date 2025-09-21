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

        if (! Hash::check($request->password, $client->password)) {
            return response()->json(['message' => 'Invalid password'], 401);
        }

        $token = $client->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login successful', 'token' => $token, 'client' => $client]);
    }

    public function register(Request $request)
    {

        $request->validate([
            'full_name' => 'required',
            'phone' => 'required',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        try {
            $client = \App\Models\Client::create($request->all());
            $result = $client;

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
