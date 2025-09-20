<x-layouts.app title="Главная">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 text-center dark:bg-gray-800/90 dark:border-gray-600/30">
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-jost-bold text-dark-gray-500 mb-8 dark:text-gray-100">
                <span
                    class="bg-gradient-to-r from-blue-500 to-dark-blue-500 bg-clip-text text-transparent">ЗАТОЧКА.ТСК</span>
                -
                профессиональная заточка инструментов
            </h1>
            <p
                class="text-xl sm:text-2xl lg:text-3xl font-jost-regular text-gray-500 mb-12 dark:text-gray-300 max-w-4xl mx-auto">
                Заточка маникюрных, парикмахерских, грумерских инструментов и ремонт оборудования. Более 5 лет опыта и
                более 30 000 восстановленных инструментов.
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Заточка инструментов -->
            <div
                class="bg-blue-50/80 backdrop-blur-lg rounded-3xl p-10 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-20 h-20 bg-blue-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">✂️</span>
                </div>
                <h3 class="text-2xl font-jost-bold text-blue-500 mb-4 dark:text-blue-400">Заточка инструментов</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="text-lg font-jost-bold text-blue-600 dark:text-blue-300 mb-2">Маникюр и подология:
                        </h4>
                        <p class="text-blue-600 dark:text-blue-300 font-jost-regular">ножницы, кусачки, твизеры, пушеры
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-blue-600 dark:text-blue-300 mb-2">Парикмахеры/барберы:
                        </h4>
                        <p class="text-blue-600 dark:text-blue-300 font-jost-regular">прямые, конвекс, филировочные
                            ножницы, машинки</p>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-blue-600 dark:text-blue-300 mb-2">Грумеры:</h4>
                        <p class="text-blue-600 dark:text-blue-300 font-jost-regular">ножницы, машинки для стрижки
                            шерсти</p>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-blue-600 dark:text-blue-300 mb-2">Лешмейкеры/бровисты:
                        </h4>
                        <p class="text-blue-600 dark:text-blue-300 font-jost-regular">пинцеты</p>
                    </div>
                </div>
            </div>

            <!-- Ремонт оборудования -->
            <div
                class="bg-pink-50/80 backdrop-blur-lg rounded-3xl p-10 border border-pink-200/30 dark:bg-pink-900/30 dark:border-pink-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500 ease-out">
                <div class="w-20 h-20 bg-pink-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl">🔧</span>
                </div>
                <h3 class="text-2xl font-jost-bold text-pink-500 mb-4 dark:text-pink-400">Ремонт оборудования</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="text-lg font-jost-bold text-pink-600 dark:text-pink-300 mb-2">Диагностика
                            оборудования</h4>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-pink-600 dark:text-pink-300 mb-2">Ремонт машинок для
                            стрижки</h4>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-pink-600 dark:text-pink-300 mb-2">Ремонт фенов</h4>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-pink-600 dark:text-pink-300 mb-2">Ремонт электрических
                            ножниц</h4>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-pink-600 dark:text-pink-300 mb-2">Ремонт триммеров</h4>
                    </div>
                    <div>
                        <h4 class="text-lg font-jost-bold text-pink-600 dark:text-pink-300 mb-2">Ремонт ультразвуковых
                            ванн</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Дополнительные услуги -->
        <div class="mt-12">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-jost-bold text-dark-gray-500 mb-4 dark:text-gray-100">Дополнительные услуги
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Гарантия -->
                <div
                    class="bg-light-pink-50/80 backdrop-blur-lg rounded-3xl p-6 border border-light-pink-200/30 dark:bg-light-pink-900/30 dark:border-light-pink-800/20 text-center">
                    <div
                        class="w-16 h-16 bg-light-pink-400/20 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🛡️</span>
                    </div>
                    <h4 class="text-lg font-jost-bold text-light-pink-400 mb-2 dark:text-light-pink-400">Гарантия</h4>
                    <p class="text-light-pink-600 dark:text-light-pink-300 font-jost-regular text-sm">90 дней гарантии
                        на все виды ремонта</p>
                </div>

                <!-- Срочный ремонт -->
                <div
                    class="bg-green-50/80 backdrop-blur-lg rounded-3xl p-6 border border-green-200/30 dark:bg-green-900/30 dark:border-green-800/20 text-center">
                    <div class="w-16 h-16 bg-green-500/20 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">⚡</span>
                    </div>
                    <h4 class="text-lg font-jost-bold text-green-500 mb-2 dark:text-green-400">Срочный ремонт</h4>
                    <p class="text-green-600 dark:text-green-300 font-jost-regular text-sm">Срочный ремонт в течение 24
                        часов</p>
                </div>

                <!-- Бесплатная доставка -->
                <div
                    class="bg-blue-50/80 backdrop-blur-lg rounded-3xl p-6 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20 text-center">
                    <div class="w-16 h-16 bg-blue-500/20 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🚚</span>
                    </div>
                    <h4 class="text-lg font-jost-bold text-blue-500 mb-2 dark:text-blue-400">Бесплатная доставка</h4>
                    <p class="text-blue-600 dark:text-blue-300 font-jost-regular text-sm">Доставка в обе стороны
                        бесплатная</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-800/90 dark:border-gray-600/30">
            <div class="text-center mb-12">
                <h2 class="text-4xl sm:text-5xl font-jost-bold text-dark-gray-500 mb-6 dark:text-gray-100">Как мы
                    работаем</h2>
                <p class="text-xl font-jost-regular text-gray-500 dark:text-gray-400">
                    Профессиональный подход к каждому инструменту
                </p>
            </div>

            <!-- Временная шкала процесса -->
            <workflow></workflow>
        </div>
    </div>



    <!-- Delivery Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div class="text-center mb-12">
            <h2 class="text-4xl sm:text-5xl font-jost-bold text-dark-gray-500 mb-6 dark:text-gray-100">Оставить заявку
                прямо сейчас
            </h2>
        </div>
        <div
            class="bg-white/85 dark:bg-gray-800/90 backdrop-blur-2xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/25 dark:border-gray-600/30">
            <order-form></order-form>
        </div>
    </div>
    <!-- CTA Section -->
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-gradient-to-r from-blue-500/90 to-dark-blue-500/90 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-blue-500/30 text-center">
            <h2 class="text-4xl sm:text-5xl font-jost-bold text-white mb-6">
                Готовы вернуть инструментам остроту?
            </h2>
            <p class="text-xl font-jost-regular text-blue-100 mb-8 max-w-2xl mx-auto">
                Свяжитесь с нами прямо сейчас и получите консультацию по вашим инструментам
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="{{ route('delivery') }}"
                    class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue">
                    🚚 Заказать доставку
                </a>
                <a href="{{ route('contacts') }}"
                    class="bg-white/20 backdrop-blur-md hover:bg-white/30 text-white px-10 py-5 rounded-2xl font-jost-bold text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform border border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue">
                    📞 Связаться с нами
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
