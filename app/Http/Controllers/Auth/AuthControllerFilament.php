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
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Простая авторизация через стандартный Laravel Auth
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $userRoles = $user->getRoles();

                // Проверяем роли и перенаправляем
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
            } else {
                return back()->withErrors(['email' => 'Неверные учетные данные']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Произошла ошибка при авторизации: ' . $e->getMessage()]);
        }
    }
}
