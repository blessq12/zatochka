<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Перенаправляем в единую админ-панель
        if ($user) {
            return redirect('/admin');
        }

        return redirect()->intended('/');
    }
}
