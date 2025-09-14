<x-layouts.app title="Контакты">
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-8 dark:text-gray-100">Контакты</h1>
            <p class="text-xl sm:text-2xl text-gray-700 mb-12 dark:text-gray-300">
                Свяжитесь с нами любым удобным способом. Мы всегда готовы помочь с вашими инструментами.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div
                    class="bg-blue-50/80 backdrop-blur-lg rounded-2xl p-10 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                    <h2 class="text-2xl font-semibold text-blue-700 mb-6 dark:text-blue-400">Контактная информация</h2>
                    <div class="space-y-6">
                        <div class="flex items-center">
                            <span class="text-lg text-blue-600 dark:text-blue-300 font-medium">Телефон:</span>
                            <span class="ml-4 text-lg text-blue-600 dark:text-blue-300">+7 (xxx) xxx-xx-xx</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-lg text-blue-600 dark:text-blue-300 font-medium">Email:</span>
                            <span class="ml-4 text-lg text-blue-600 dark:text-blue-300">info@zatochka.ru</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-lg text-blue-600 dark:text-blue-300 font-medium">Адрес:</span>
                            <span class="ml-4 text-lg text-blue-600 dark:text-blue-300">г. Город, ул. Улица, д. 1</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-green-50/80 backdrop-blur-lg rounded-2xl p-10 border border-green-200/30 dark:bg-green-900/30 dark:border-green-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                    <h2 class="text-2xl font-semibold text-green-700 mb-6 dark:text-green-400">Режим работы</h2>
                    <div class="space-y-4 text-lg text-green-600 dark:text-green-300">
                        <div>Пн-Пт: 9:00 - 18:00</div>
                        <div>Сб: 10:00 - 16:00</div>
                        <div>Вс: Выходной</div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">Страница в разработке...</p>
                <a href="tel:+7xxxxxxxxx"
                    class="bg-blue-600/90 backdrop-blur-md hover:bg-blue-700/90 text-white px-10 py-5 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90">
                    Позвонить нам
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
