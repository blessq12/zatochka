<x-layouts.app title="Главная">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20 text-center">
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-gray-900 mb-8 dark:text-gray-100">
                Профессиональная <span
                    class="bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">заточка</span>
                инструментов
            </h1>
            <p class="text-xl sm:text-2xl lg:text-3xl text-gray-700 mb-12 dark:text-gray-300 max-w-4xl mx-auto">
                Восстановим остроту ваших ножей, ножниц и инструментов. Качественная работа с гарантией результата.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="{{ route('sharpening') }}"
                    class="bg-blue-600/90 backdrop-blur-md hover:bg-blue-700/90 text-white px-10 py-5 rounded-2xl font-semibold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform">
                    Заказать заточку
                </a>
                <a href="{{ route('contacts') }}"
                    class="bg-white/60 backdrop-blur-md hover:bg-white/80 text-gray-900 px-10 py-5 rounded-2xl font-semibold text-xl transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20">
                    Связаться с нами
                </a>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div class="text-center mb-12">
            <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 dark:text-gray-100">Наши услуги</h2>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                Полный спектр услуг по заточке, ремонту и восстановлению инструментов
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
                class="bg-blue-50/80 backdrop-blur-lg rounded-3xl p-10 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-16 h-16 bg-blue-500/20 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">🔪</span>
                </div>
                <h3 class="text-2xl font-bold text-blue-700 mb-4 dark:text-blue-400">Заточка ножей</h3>
                <p class="text-lg text-blue-600 dark:text-blue-300 mb-6">
                    Профессиональная заточка кухонных, охотничьих и профессиональных ножей на современном оборудовании
                </p>
                <ul class="space-y-2 text-blue-600 dark:text-blue-300">
                    <li>• Кухонные ножи</li>
                    <li>• Охотничьи ножи</li>
                    <li>• Профессиональные ножи</li>
                    <li>• Ножи для мясорубок</li>
                </ul>
            </div>

            <div
                class="bg-green-50/80 backdrop-blur-lg rounded-3xl p-10 border border-green-200/30 dark:bg-green-900/30 dark:border-green-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-16 h-16 bg-green-500/20 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">✂️</span>
                </div>
                <h3 class="text-2xl font-bold text-green-700 mb-4 dark:text-green-400">Заточка ножниц</h3>
                <p class="text-lg text-green-600 dark:text-green-300 mb-6">
                    Восстановление режущих свойств ножниц всех типов: от бытовых до профессиональных
                </p>
                <ul class="space-y-2 text-green-600 dark:text-green-300">
                    <li>• Парикмахерские ножницы</li>
                    <li>• Портновские ножницы</li>
                    <li>• Садовые ножницы</li>
                    <li>• Бытовые ножницы</li>
                </ul>
            </div>

            <div
                class="bg-yellow-50/80 backdrop-blur-lg rounded-3xl p-10 border border-yellow-200/30 dark:bg-yellow-900/30 dark:border-yellow-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-16 h-16 bg-yellow-500/20 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">🔧</span>
                </div>
                <h3 class="text-2xl font-bold text-yellow-700 mb-4 dark:text-yellow-400">Ремонт инструментов</h3>
                <p class="text-lg text-yellow-600 dark:text-yellow-300 mb-6">
                    Восстановление и ремонт поврежденного инструмента с заменой деталей
                </p>
                <ul class="space-y-2 text-yellow-600 dark:text-yellow-300">
                    <li>• Замена ручек</li>
                    <li>• Восстановление кромок</li>
                    <li>• Замена крепежа</li>
                    <li>• Реставрация покрытий</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <div class="text-center mb-12">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 dark:text-gray-100">Почему выбирают нас
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    Мы гарантируем качество и надежность наших услуг
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">⚡</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 dark:text-gray-100">Быстро</h3>
                    <p class="text-gray-600 dark:text-gray-400">Заточка за 1-2 дня</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-green-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">✅</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 dark:text-gray-100">Качественно</h3>
                    <p class="text-gray-600 dark:text-gray-400">Современное оборудование</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-yellow-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">🛡️</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 dark:text-gray-100">Гарантия</h3>
                    <p class="text-gray-600 dark:text-gray-400">30 дней на все работы</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-purple-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">🚚</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 dark:text-gray-100">Доставка</h3>
                    <p class="text-gray-600 dark:text-gray-400">Бесплатно по городу</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-gradient-to-r from-blue-600/90 to-blue-800/90 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-blue-500/30 text-center">
            <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                Готовы заточить ваши инструменты?
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Свяжитесь с нами прямо сейчас и получите консультацию по вашим инструментам
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="tel:+7xxxxxxxxx"
                    class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-semibold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30">
                    📞 Позвонить
                </a>
                <a href="{{ route('contacts') }}"
                    class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-semibold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30">
                    📧 Написать
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
