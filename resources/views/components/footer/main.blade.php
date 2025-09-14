<footer class="bg-white/80 backdrop-blur-xl border-t border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
    <div class="container mx-auto py-16 px-8 sm:px-12 lg:px-16 xl:px-20">
        <!-- Логотип и описание -->
        <div class="flex items-center space-x-4 mb-12">
            <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center shadow-lg">
                <span class="text-white font-jost-bold text-xl">З</span>
            </div>
            <div>
                <span class="text-2xl font-jost-bold text-dark-blue-500 dark:text-blue-300">
                    Заточка
                </span>
                <p class="text-lg font-jost-regular text-gray-500 dark:text-gray-300 mt-1">
                    Профессиональная заточка и ремонт инструментов
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <h3 class="text-lg font-jost-bold text-dark-blue-500 dark:text-blue-300 mb-6">Компания</h3>
                <ul class="space-y-4">
                    <li><a href="{{ route('contacts') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Контакты</a>
                    </li>
                    <li><a href="{{ route('help') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Помощь</a>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-jost-bold text-dark-blue-500 dark:text-blue-300 mb-6">Услуги</h3>
                <ul class="space-y-4">
                    <li><a href="{{ route('sharpening') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Заточка</a>
                    </li>
                    <li><a href="{{ route('repair') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Ремонт</a>
                    </li>
                    <li><a href="{{ route('delivery') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Доставка</a>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-jost-bold text-dark-blue-500 dark:text-blue-300 mb-6">
                    Правовая информация</h3>
                <ul class="space-y-4">
                    <li><a href="{{ route('privacy-policy') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Политика
                            конфиденциальности</a></li>
                    <li><a href="{{ route('terms-of-service') }}"
                            class="text-lg font-jost-regular text-gray-500 hover:text-blue-500 transition-all duration-300 dark:text-gray-400 dark:hover:text-blue-400">Условия
                            использования</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-jost-bold text-dark-blue-500 dark:text-blue-300 mb-6">
                    Контакты</h3>
                <ul class="space-y-4">
                    <li class="text-lg font-jost-regular text-gray-500 dark:text-gray-400">Телефон: +7 (xxx) xxx-xx-xx
                    </li>
                    <li class="text-lg font-jost-regular text-gray-500 dark:text-gray-400">Email: info@zatochka.ru</li>
                </ul>
            </div>
        </div>
        <div class="mt-16 border-t border-white/20 pt-12 dark:border-gray-700/20">
            <div class="flex flex-col sm:flex-row items-center justify-between">
                <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-jost-bold text-sm">З</span>
                    </div>
                    <span class="text-xl font-jost-bold text-dark-blue-500 dark:text-blue-300">Заточка</span>
                </div>
                <p class="text-lg font-jost-regular text-gray-500 text-center dark:text-gray-400">
                    &copy; {{ date('Y') }} Все права защищены.
                </p>
            </div>
        </div>
    </div>
</footer>
