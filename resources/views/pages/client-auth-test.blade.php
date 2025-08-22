@extends('layouts.app')

@section('title', 'Тест аутентификации клиентов')
@section('description', 'Тестовая страница для проверки компонентов аутентификации клиентов')

@section('content')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="max-w-md mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <client-auth @auth-success="handleAuthSuccess" @logout="handleLogout"
                    @verification-complete="handleVerificationComplete" @edit-profile="handleEditProfile" />
            </div>
        </div>
    </div>
@endsection
