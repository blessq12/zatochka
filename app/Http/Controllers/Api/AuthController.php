<?php

namespace App\Http\Controllers\Api;

use App\Application\UseCases\Auth\Api\ApiRegisterUseCase;
use App\Application\UseCases\Auth\Api\ApiLoginUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        try {
            $useCase = app(ApiLoginUseCase::class);
            $result = $useCase->loadData($request->all())->validate()->execute();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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
            $useCase = app(ApiRegisterUseCase::class);
            $result = $useCase->loadData($request->all())->validate()->execute();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
