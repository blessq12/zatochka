<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FilamentAuthController extends Controller
{
    public function login()
    {
        if (Auth::guard('filament')->check()) {

            $user = Auth::guard('filament')->user();
            return redirect()->route(
                $user->role == 'manager' ?
                    'filament.crm.pages.dashboard' :
                    'filament.workshop.pages.dashboard'
            );
        }

        return view('filament.auth.login');
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('filament')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('filament')->user();

            if (!$this->hasCrmAccess($user)) {
                Auth::guard('filament')->logout();
                throw ValidationException::withMessages([
                    'email' => 'У вас нет доступа к панели управления.',
                ]);
            }

            $request->session()->regenerate();


            return redirect()->route(
                $user->role == 'manager' ?
                    'filament.crm.pages.dashboard' :
                    'filament.workshop.pages.dashboard'
            );
        }

        throw ValidationException::withMessages([
            'email' => 'Неверные учетные данные.',
        ]);
    }

    /**
     * Проверяет, имеет ли пользователь доступ к CRM
     */
    private function hasCrmAccess($user): bool
    {
        $allowedRoles = ['manager', 'master'];

        return in_array($user->role, $allowedRoles);
    }

    public function logout()
    {
        Auth::guard('filament')->logout();
        return redirect()->route('crm.login');
    }
}
