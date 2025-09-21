<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthControllerFilament extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginPost(Request $request)
    {
        try {
            $credentials = request()->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Используем Use Case для авторизации
            /** @var \App\Application\UseCases\Auth\AuthenticateUserUseCase $authUseCase */
            $authUseCase = app(\App\Application\UseCases\Auth\AuthenticateUserUseCase::class);
            $user = $authUseCase->loadData($credentials)->validate()->executeWithLaravelAuth();

            // Перенаправляем в зависимости от ролей пользователя
            $userRoles = $user->getRoles();

            if (in_array('manager', $userRoles) && in_array('master', $userRoles)) {
                return redirect('/manager');
            } elseif (in_array('manager', $userRoles)) {
                return redirect('/manager');
            } elseif (in_array('master', $userRoles)) {
                return redirect('/master');
            } else {
                Auth::logout();

                return back()->withErrors(['email' => 'У пользователя не назначены роли']);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Произошла ошибка при авторизации']);
        }
    }
}
