<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/**
 * Совсем скоро тут будут маршруты для веб-приложения
 */

Route::get('/', function () {
    return redirect('/login');
});

// Единая страница авторизации
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
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
})->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
