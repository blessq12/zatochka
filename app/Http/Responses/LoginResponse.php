<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {

        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->hasRole('manager')) {
            return redirect()->route('filament.manager.pages.dashboard');
        }

        if ($user->hasRole('master')) {
            return redirect()->route('filament.master.pages.dashboard');
        }

        return redirect()->intended('/');
    }
}
