<footer class="bg-white/80 backdrop-blur-xl border-t border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
    <div class="container mx-auto py-16 px-8 sm:px-12 lg:px-16 xl:px-20">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 tracking-wider uppercase dark:text-gray-300 mb-6">Компания
                </h3>
                <ul class="space-y-6">
                    <li><a href="{{ route('contacts') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Контакты</a>
                    </li>
                    <li><a href="{{ route('help') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Помощь</a>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 tracking-wider uppercase dark:text-gray-300 mb-6">Услуги
                </h3>
                <ul class="space-y-6">
                    <li><a href="{{ route('sharpening') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Заточка</a>
                    </li>
                    <li><a href="{{ route('repair') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Ремонт</a>
                    </li>
                    <li><a href="{{ route('delivery') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Доставка</a>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 tracking-wider uppercase dark:text-gray-300 mb-6">
                    Правовая информация</h3>
                <ul class="space-y-6">
                    <li><a href="{{ route('privacy-policy') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Политика
                            конфиденциальности</a></li>
                    <li><a href="{{ route('terms-of-service') }}"
                            class="text-lg text-gray-500 hover:text-gray-900 transition-all duration-300 dark:text-gray-400 dark:hover:text-gray-100">Условия
                            использования</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 tracking-wider uppercase dark:text-gray-300 mb-6">
                    Контакты</h3>
                <ul class="space-y-6">
                    <li class="text-lg text-gray-500 dark:text-gray-400">Телефон: +7 (xxx) xxx-xx-xx</li>
                    <li class="text-lg text-gray-500 dark:text-gray-400">Email: info@example.com</li>
                </ul>
            </div>
        </div>
        <div class="mt-16 border-t border-white/20 pt-12 dark:border-gray-700/20">
            <p class="text-lg text-gray-500 text-center dark:text-gray-400">
                &copy; {{ date('Y') }} Заточка. Все права защищены.
            </p>
        </div>
    </div>
</footer>
