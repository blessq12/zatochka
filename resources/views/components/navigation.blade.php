@props([
    'navigation' => [
        [
            'name' => 'Заточка инструмента',
            'route' => 'sharpening',
            'href' => '/sharpening',
        ],
        [
            'name' => 'Ремонт инструмента',
            'route' => 'repair',
            'href' => '/repair',
        ],
        [
            'name' => 'Доставка',
            'route' => 'delivery',
            'href' => '/delivery',
        ],
        [
            'name' => 'Контакты',
            'route' => 'contacts',
            'href' => '/contacts',
        ],
        [
            'name' => 'Личный кабинет',
            'route' => 'client-dashboard',
            'href' => '/dashboard',
        ],
    ],
])

<!-- Навигация -->
<nav
    class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-lg border-b border-gray-200 dark:border-gray-800 w-full font-jost">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">
            <!-- Логотип -->
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="/logo.svg" alt="Заточка ТСК" class="h-10 w-auto">
            </a>

            <!-- Десктопное меню -->
            <div class="hidden md:flex items-center space-x-8">
                @if (!Route::is('home'))
                    <a href="{{ route('home') }}"
                        class="relative font-bold text-gray-700 dark:text-gray-300 hover:text-accent dark:hover:text-accent-light transition-colors duration-300 {{ Route::is('home') ? 'text-accent dark:text-accent-light' : '' }}">
                        <span class="flex items-center">
                            Главная
                        </span>
                        @if (Route::is('home'))
                            <div class="absolute -bottom-2 left-0 right-0 h-0.5 bg-accent dark:bg-accent-light"></div>
                        @endif
                    </a>
                @endif

                @foreach ($navigation as $item)
                    @if ($item['route'] === 'client-dashboard')
                        <user-profile-toggle :href="'{{ route($item['route']) }}'"
                            :is-active="{{ Route::is($item['route']) ? 'true' : 'false' }}"
                            :is-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
                        </user-profile-toggle>
                    @else
                        <a href="{{ route($item['route']) }}"
                            class="relative font-bold text-gray-700 dark:text-gray-300 hover:text-accent dark:hover:text-accent-light transition-colors duration-300 {{ Route::is($item['route']) ? 'text-accent dark:text-accent-light' : '' }}">
                            <span class="flex items-center">
                                {{ $item['name'] }}
                            </span>
                            @if (Route::is($item['route']))
                                <div class="absolute -bottom-2 left-0 right-0 h-0.5 bg-accent dark:bg-accent-light">
                                </div>
                            @endif
                        </a>
                    @endif
                @endforeach

                <theme-toggle></theme-toggle>
            </div>

            <!-- Мобильное меню -->
            <div class="md:hidden">
                <mobile-menu :navigation='@json($navigation)' :contacts='@json($contacts ?? [])'>
                </mobile-menu>
            </div>
        </div>
    </div>
</nav>
