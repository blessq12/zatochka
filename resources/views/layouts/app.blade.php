<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Заточка')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800">
                                Заточка
                            </a>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('home') }}"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Главная
                            </a>
                            <a href="{{ route('sharpening') }}"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Заточка
                            </a>
                            <a href="{{ route('repair') }}"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Ремонт
                            </a>
                            <a href="{{ route('delivery') }}"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Доставка
                            </a>
                            <a href="{{ route('contacts') }}"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Контакты
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('login') }}"
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                            Войти
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Компания</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="{{ route('contacts') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Контакты</a></li>
                            <li><a href="{{ route('help') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Помощь</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Услуги</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="{{ route('sharpening') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Заточка</a></li>
                            <li><a href="{{ route('repair') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Ремонт</a></li>
                            <li><a href="{{ route('delivery') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Доставка</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Правовая информация
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="{{ route('privacy-policy') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Политика конфиденциальности</a>
                            </li>
                            <li><a href="{{ route('terms-of-service') }}"
                                    class="text-base text-gray-500 hover:text-gray-900">Условия использования</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Контакты</h3>
                        <ul class="mt-4 space-y-4">
                            <li class="text-base text-gray-500">Телефон: +7 (xxx) xxx-xx-xx</li>
                            <li class="text-base text-gray-500">Email: info@example.com</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 pt-8">
                    <p class="text-base text-gray-400 text-center">
                        &copy; {{ date('Y') }} Заточка. Все права защищены.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
