<x-layouts.app title="Главная">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20 text-center">
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-jost-bold text-dark-gray-500 mb-8 dark:text-gray-100">
                Профессиональная <span
                    class="bg-gradient-to-r from-blue-500 to-dark-blue-500 bg-clip-text text-transparent">заточка</span>
                инструментов
            </h1>
            <p
                class="text-xl sm:text-2xl lg:text-3xl font-jost-regular text-gray-500 mb-12 dark:text-gray-300 max-w-4xl mx-auto">
                Восстановим остроту ваших ножей, ножниц и инструментов. Качественная работа с гарантией результата.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="{{ route('sharpening') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    Заказать заточку
                </a>
                <a href="{{ route('contacts') }}"
                    class="bg-white/60 backdrop-blur-md hover:bg-white/80 text-dark-gray-500 px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl border border-white/20 dark:bg-gray-800/60 dark:hover:bg-gray-700/80 dark:text-gray-100 dark:border-gray-700/20 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    Связаться с нами
                </a>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div class="text-center mb-12">
            <h2 class="text-4xl sm:text-5xl font-jost-bold text-dark-gray-500 mb-6 dark:text-gray-100">Наши услуги</h2>
            <p class="text-xl font-jost-regular text-gray-500 dark:text-gray-400 max-w-3xl mx-auto">
                Полный спектр услуг по заточке, ремонту и восстановлению инструментов
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div
                class="bg-blue-50/80 backdrop-blur-lg rounded-3xl p-10 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-16 h-16 bg-blue-500/20 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">🔪</span>
                </div>
                <h3 class="text-2xl font-jost-bold text-blue-500 mb-4 dark:text-blue-400">Заточка ножей</h3>
                <p class="text-lg font-jost-regular text-blue-600 dark:text-blue-300 mb-6">
                    Профессиональная заточка кухонных, охотничьих и профессиональных ножей на современном оборудовании
                </p>
                <ul class="space-y-2 text-blue-600 dark:text-blue-300 font-jost-regular">
                    <li>• Кухонные ножи</li>
                    <li>• Охотничьи ножи</li>
                    <li>• Профессиональные ножи</li>
                    <li>• Ножи для мясорубок</li>
                </ul>
            </div>

            <div
                class="bg-pink-50/80 backdrop-blur-lg rounded-3xl p-10 border border-pink-200/30 dark:bg-pink-900/30 dark:border-pink-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-16 h-16 bg-pink-500/20 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">✂️</span>
                </div>
                <h3 class="text-2xl font-jost-bold text-pink-500 mb-4 dark:text-pink-400">Заточка ножниц</h3>
                <p class="text-lg font-jost-regular text-pink-600 dark:text-pink-300 mb-6">
                    Восстановление режущих свойств ножниц всех типов: от бытовых до профессиональных
                </p>
                <ul class="space-y-2 text-pink-600 dark:text-pink-300 font-jost-regular">
                    <li>• Парикмахерские ножницы</li>
                    <li>• Портновские ножницы</li>
                    <li>• Садовые ножницы</li>
                    <li>• Бытовые ножницы</li>
                </ul>
            </div>

            <div
                class="bg-light-pink-50/80 backdrop-blur-lg rounded-3xl p-10 border border-light-pink-200/30 dark:bg-light-pink-900/30 dark:border-light-pink-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-16 h-16 bg-light-pink-400/20 rounded-2xl flex items-center justify-center mb-6">
                    <span class="text-3xl">🔧</span>
                </div>
                <h3 class="text-2xl font-jost-bold text-light-pink-400 mb-4 dark:text-light-pink-400">Ремонт
                    инструментов
                </h3>
                <p class="text-lg font-jost-regular text-light-pink-600 dark:text-light-pink-300 mb-6">
                    Восстановление и ремонт поврежденного инструмента с заменой деталей
                </p>
                <ul class="space-y-2 text-light-pink-600 dark:text-light-pink-300 font-jost-regular">
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
                <h2 class="text-4xl sm:text-5xl font-jost-bold text-dark-gray-500 mb-6 dark:text-gray-100">Почему
                    выбирают
                    нас
                </h2>
                <p class="text-xl font-jost-regular text-gray-500 dark:text-gray-400">
                    Мы гарантируем качество и надежность наших услуг
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">⚡</span>
                    </div>
                    <h3 class="text-xl font-jost-bold text-dark-gray-500 mb-3 dark:text-gray-100">Быстро</h3>
                    <p class="text-gray-500 font-jost-regular dark:text-gray-400">Заточка за 1-2 дня</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-pink-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">✅</span>
                    </div>
                    <h3 class="text-xl font-jost-bold text-dark-gray-500 mb-3 dark:text-gray-100">Качественно</h3>
                    <p class="text-gray-500 font-jost-regular dark:text-gray-400">Современное оборудование</p>
                </div>

                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-light-pink-400/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">🛡️</span>
                    </div>
                    <h3 class="text-xl font-jost-bold text-dark-gray-500 mb-3 dark:text-gray-100">Гарантия</h3>
                    <p class="text-gray-500 font-jost-regular dark:text-gray-400">30 дней на все работы</p>
                </div>

                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-dark-blue-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl">🚚</span>
                    </div>
                    <h3 class="text-xl font-jost-bold text-dark-gray-500 mb-3 dark:text-gray-100">Доставка</h3>
                    <p class="text-gray-500 font-jost-regular dark:text-gray-400">Бесплатно по городу</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Color Palette Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div class="text-center mb-12">
            <h2 class="text-4xl sm:text-5xl font-jost-bold text-dark-gray-500 mb-6 dark:text-gray-100">Фирменные цвета
            </h2>
            <p class="text-xl font-jost-regular text-gray-500 dark:text-gray-400 max-w-3xl mx-auto">
                Палитра цветов Заточка.ТСК - современная и профессиональная
            </p>
        </div>

        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <!-- Основные цвета -->
            <div class="mb-16">
                <h3 class="text-2xl font-jost-bold text-dark-gray-500 mb-8 dark:text-gray-100 text-center">Основные
                    цвета</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <!-- ТЁМНО-СИНИЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-dark-blue-500 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">ТЁМНО-СИНИЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#003859</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">dark-blue-500</div>
                    </div>

                    <!-- СИНИЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-blue-500 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">СИНИЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#046490</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">blue-500</div>
                    </div>

                    <!-- РОЗОВЫЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-pink-500 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">РОЗОВЫЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#c3006b</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">pink-500</div>
                    </div>

                    <!-- ТЁМНО-СЕРЫЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-dark-gray-500 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">ТЁМНО-СЕРЫЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#3c3c3b</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">dark-gray-500</div>
                    </div>
                </div>
            </div>

            <!-- Дополнительные цвета -->
            <div class="mb-16">
                <h3 class="text-2xl font-jost-bold text-dark-gray-500 mb-8 dark:text-gray-100 text-center">
                    Дополнительные цвета</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <!-- СВЕТЛО-РОЗОВЫЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-light-pink-400 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">СВЕТЛО-РОЗОВЫЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#e991bd</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">light-pink-400</div>
                    </div>

                    <!-- СВЕТЛО-СЕРЫЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-light-gray-400 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">СВЕТЛО-СЕРЫЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#d3d3d3</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">light-gray-400</div>
                    </div>

                    <!-- ЧЁРНЫЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-black rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">ЧЁРНЫЙ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">#000000</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">black</div>
                    </div>

                    <!-- БЕЛЫЙ -->
                    <div class="text-center group">
                        <div
                            class="bg-white border-2 border-gray-300 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-black font-jost-bold text-lg">БЕЛЫЙ</div>
                            <div class="text-black/80 font-jost-regular text-sm mt-2">#ffffff</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">white</div>
                    </div>
                </div>
            </div>

            <!-- Градиенты -->
            <div>
                <h3 class="text-2xl font-jost-bold text-dark-gray-500 mb-8 dark:text-gray-100 text-center">Градиенты
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center group">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-dark-blue-500 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">СИНИЙ ГРАДИЕНТ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">blue → dark-blue</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">from-blue-500
                            to-dark-blue-500</div>
                    </div>

                    <div class="text-center group">
                        <div
                            class="bg-gradient-to-r from-pink-500 to-light-pink-400 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">РОЗОВЫЙ ГРАДИЕНТ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">pink → light-pink</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">from-pink-500
                            to-light-pink-400</div>
                    </div>

                    <div class="text-center group">
                        <div
                            class="bg-gradient-to-r from-dark-gray-500 to-gray-500 rounded-3xl p-8 mb-4 shadow-lg group-hover:shadow-2xl transition-all duration-300 group-hover:scale-105">
                            <div class="text-white font-jost-bold text-lg">СЕРЫЙ ГРАДИЕНТ</div>
                            <div class="text-white/80 font-jost-regular text-sm mt-2">dark-gray → gray</div>
                        </div>
                        <div class="text-sm font-jost-medium text-gray-500 dark:text-gray-400">from-dark-gray-500
                            to-gray-500</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-gradient-to-r from-blue-500/90 to-dark-blue-500/90 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-blue-500/30 text-center">
            <h2 class="text-4xl sm:text-5xl font-jost-bold text-white mb-6">
                Готовы заточить ваши инструменты?
            </h2>
            <p class="text-xl font-jost-regular text-blue-100 mb-8 max-w-2xl mx-auto">
                Свяжитесь с нами прямо сейчас и получите консультацию по вашим инструментам
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="tel:+7xxxxxxxxx"
                    class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue">
                    📞 Позвонить
                </a>
                <a href="{{ route('contacts') }}"
                    class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue">
                    📧 Написать
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
