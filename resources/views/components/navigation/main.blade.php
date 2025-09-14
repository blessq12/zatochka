<nav
    class="bg-white/90 backdrop-blur-2xl shadow-2xl border-b border-white/30 dark:bg-gray-900/90 dark:border-gray-800/30 sticky top-0 z-50">
    <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
        <div class="flex justify-between items-center h-24">
            <!-- Логотип -->
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                        <span class="text-white font-bold text-xl">З</span>
                    </div>
                    <span
                        class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent dark:from-gray-100 dark:to-gray-300">
                        Заточка
                    </span>
                </a>
            </div>

            <!-- Навигационные ссылки -->
            <div class="hidden lg:flex items-center space-x-2">
                <a href="{{ route('home') }}"
                    class="relative px-6 py-3 rounded-2xl text-lg font-medium transition-all duration-300 group {{ request()->routeIs('home') ? 'bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100' }}">
                    <span class="relative z-10">Главная</span>
                    @if (request()->routeIs('home'))
                        <div class="absolute inset-0 bg-blue-500/10 rounded-2xl backdrop-blur-sm"></div>
                    @endif
                </a>

                <a href="{{ route('sharpening') }}"
                    class="relative px-6 py-3 rounded-2xl text-lg font-medium transition-all duration-300 group {{ request()->routeIs('sharpening') ? 'bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100' }}">
                    <span class="relative z-10">Заточка</span>
                    @if (request()->routeIs('sharpening'))
                        <div class="absolute inset-0 bg-blue-500/10 rounded-2xl backdrop-blur-sm"></div>
                    @endif
                </a>

                <a href="{{ route('repair') }}"
                    class="relative px-6 py-3 rounded-2xl text-lg font-medium transition-all duration-300 group {{ request()->routeIs('repair') ? 'bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100' }}">
                    <span class="relative z-10">Ремонт</span>
                    @if (request()->routeIs('repair'))
                        <div class="absolute inset-0 bg-blue-500/10 rounded-2xl backdrop-blur-sm"></div>
                    @endif
                </a>

                <a href="{{ route('delivery') }}"
                    class="relative px-6 py-3 rounded-2xl text-lg font-medium transition-all duration-300 group {{ request()->routeIs('delivery') ? 'bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100' }}">
                    <span class="relative z-10">Доставка</span>
                    @if (request()->routeIs('delivery'))
                        <div class="absolute inset-0 bg-blue-500/10 rounded-2xl backdrop-blur-sm"></div>
                    @endif
                </a>

                <a href="{{ route('contacts') }}"
                    class="relative px-6 py-3 rounded-2xl text-lg font-medium transition-all duration-300 group {{ request()->routeIs('contacts') ? 'bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100' }}">
                    <span class="relative z-10">Контакты</span>
                    @if (request()->routeIs('contacts'))
                        <div class="absolute inset-0 bg-blue-500/10 rounded-2xl backdrop-blur-sm"></div>
                    @endif
                </a>
            </div>

            <!-- Правая часть -->
            <div class="flex items-center space-x-4">
                <theme-toggler></theme-toggler>

                <a href="{{ route('client.dashboard') }}"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform backdrop-blur-sm">
                    Войти
                </a>
            </div>
        </div>
    </div>
</nav>
