<nav
    class="bg-white/90 backdrop-blur-xs shadow-lg border-b border-white/30 dark:bg-gray-900/90 dark:backdrop-blur-xs dark:border-gray-800/30 sticky top-0 z-50">
    <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
        <div class="flex justify-between items-center h-24">
            <!-- Логотип -->
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}"
                    class="flex items-center space-x-4 group focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-xl p-2 -m-2">
                    <div
                        class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105 group-focus:ring-2 group-focus:ring-blue-500/50">
                        <span class="text-white font-jost-bold text-xl">З</span>
                    </div>
                    <span
                        class="text-2xl font-jost-bold text-dark-blue-500 group-hover:text-blue-500 transition-colors duration-300 dark:text-blue-300 dark:group-hover:text-blue-200">
                        Заточка
                    </span>
                </a>
            </div>

            <!-- Навигационные ссылки -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="{{ route('home') }}"
                    class="text-dark-gray-500 hover:text-blue-500 hover:bg-white/50 hover:backdrop-blur-xs px-6 py-4 rounded-2xl text-lg font-jost-medium transition-all duration-300 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-800/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ request()->routeIs('home') ? 'bg-white/60 backdrop-blur-xs text-blue-700 border border-white/20 rounded-2xl dark:bg-gray-800/60 dark:backdrop-blur-xs dark:text-blue-400 dark:border-gray-700/20' : '' }}">
                    Главная
                </a>

                <a href="{{ route('sharpening') }}"
                    class="text-dark-gray-500 hover:text-blue-500 hover:bg-white/50 hover:backdrop-blur-xs px-6 py-4 rounded-2xl text-lg font-jost-medium transition-all duration-300 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-800/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ request()->routeIs('sharpening') ? 'bg-white/60 backdrop-blur-xs text-blue-700 border border-white/20 rounded-2xl dark:bg-gray-800/60 dark:backdrop-blur-xs dark:text-blue-400 dark:border-gray-700/20' : '' }}">
                    Заточка
                </a>

                <a href="{{ route('repair') }}"
                    class="text-dark-gray-500 hover:text-blue-500 hover:bg-white/50 hover:backdrop-blur-xs px-6 py-4 rounded-2xl text-lg font-jost-medium transition-all duration-300 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-800/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ request()->routeIs('repair') ? 'bg-white/60 backdrop-blur-xs text-blue-700 border border-white/20 rounded-2xl dark:bg-gray-800/60 dark:backdrop-blur-xs dark:text-blue-400 dark:border-gray-700/20' : '' }}">
                    Ремонт
                </a>

                <a href="{{ route('delivery') }}"
                    class="text-dark-gray-500 hover:text-blue-500 hover:bg-white/50 hover:backdrop-blur-xs px-6 py-4 rounded-2xl text-lg font-jost-medium transition-all duration-300 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-800/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ request()->routeIs('delivery') ? 'bg-white/60 backdrop-blur-xs text-blue-700 border border-white/20 rounded-2xl dark:bg-gray-800/60 dark:backdrop-blur-xs dark:text-blue-400 dark:border-gray-700/20' : '' }}">
                    Доставка
                </a>

                <a href="{{ route('contacts') }}"
                    class="text-dark-gray-500 hover:text-blue-500 hover:bg-white/50 hover:backdrop-blur-xs px-6 py-4 rounded-2xl text-lg font-jost-medium transition-all duration-300 dark:text-gray-300 dark:hover:text-blue-400 dark:hover:bg-gray-800/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 {{ request()->routeIs('contacts') ? 'bg-white/60 backdrop-blur-xs text-blue-700 border border-white/20 rounded-2xl dark:bg-gray-800/60 dark:backdrop-blur-xs dark:text-blue-400 dark:border-gray-700/20' : '' }}">
                    Контакты
                </a>
            </div>

            <!-- Правая часть -->
            <div class="flex items-center space-x-4">
                <theme-toggler></theme-toggler>
                <dashboard-button></dashboard-button>
            </div>
        </div>
    </div>
</nav>
