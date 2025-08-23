<x-app-layout title="Контакты">
    <!-- Hero секция -->
    <x-page-hero title="Наши <span class='text-accent'>контакты</span>"
        description="Мы всегда рады помочь вам с заточкой и ремонтом инструментов. Свяжитесь с нами любым удобным способом."
        :breadcrumbs="[['name' => 'Контакты', 'href' => route('contacts')]]" />

    <!-- Основные контакты -->
    <section class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Основные контакты</h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">Выберите удобный способ связи</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Телефон -->
                <div class="group relative">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-accent to-pink-600 rounded-3xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                    </div>
                    <div
                        class="relative bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-accent to-pink-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="mdi mdi-phone text-white text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Телефон</h3>
                        <a href="tel:+79832335907"
                            class="text-2xl font-bold text-accent dark:text-accent-light hover:text-accent/80 transition-colors duration-300 block mb-2">
                            +7 (983) 233-59-07
                        </a>
                        <p class="text-gray-600 dark:text-gray-400 text-lg">Максим</p>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">Ежедневно с 9:00 до 18:00</p>
                    </div>
                </div>

                <!-- Email -->
                <div class="group relative">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-3xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                    </div>
                    <div
                        class="relative bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="mdi mdi-email text-white text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Email</h3>
                        <a href="mailto:zatochka.tsk@yandex.ru"
                            class="text-lg text-accent dark:text-accent-light hover:text-accent/80 transition-colors duration-300 block break-all">
                            zatochka.tsk@yandex.ru
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">Ответим в течение 2 часов</p>
                    </div>
                </div>

                <!-- Адрес -->
                <div class="group relative">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-green-500 to-emerald-500 rounded-3xl blur opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                    </div>
                    <div
                        class="relative bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="mdi mdi-map-marker text-white text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Адрес</h3>
                        <div class="space-y-2 text-gray-700 dark:text-gray-300">
                            <p class="font-semibold">Пр. Ленина 169/пер. Карповский 12</p>
                            <p class="text-sm">Вход со стороны Ленина</p>
                            <p class="text-sm font-medium">Ориентир — магазин «Тайга»</p>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-4">Рабочие дни: Пн-Сб 9:00-18:00</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Социальные сети -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Мы в социальных сетях</h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">Следите за нашими новостями и акциями</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Telegram -->
                <a href="#" class="group">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700 hover:border-[#0088cc] dark:hover:border-[#0088cc]">
                        <div
                            class="w-16 h-16 bg-[#0088cc] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="mdi mdi-telegram text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Telegram-канал</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Подписывайтесь на наш канал для получения
                            новостей и акций</p>
                        <div class="flex items-center text-[#0088cc] font-semibold">
                            <span>Подписаться</span>
                            <i
                                class="mdi mdi-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </div>
                    </div>
                </a>

                <!-- VK -->
                <a href="#" class="group">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700 hover:border-[#4C75A3] dark:hover:border-[#4C75A3]">
                        <div
                            class="w-16 h-16 bg-[#4C75A3] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="mdi mdi-vk text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">ВКонтакте</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Присоединяйтесь к нашей группе ВКонтакте</p>
                        <div class="flex items-center text-[#4C75A3] font-semibold">
                            <span>Присоединиться</span>
                            <i
                                class="mdi mdi-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </div>
                    </div>
                </a>

                <!-- WhatsApp -->
                <a href="https://wa.me/79832335907" class="group">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700 hover:border-[#25D366] dark:hover:border-[#25D366]">
                        <div
                            class="w-16 h-16 bg-[#25D366] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="mdi mdi-whatsapp text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">WhatsApp</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Напишите нам в WhatsApp для быстрой связи</p>
                        <div class="flex items-center text-[#25D366] font-semibold">
                            <span>Написать</span>
                            <i
                                class="mdi mdi-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Карта и как добраться -->
    <section class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Как нас найти</h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">Удобное расположение в центре города</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Карта -->
                <div class="group">
                    <div
                        class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-3xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-600">
                        <div
                            class="w-full h-[400px] flex flex-col items-center justify-center bg-white dark:bg-gray-900 rounded-2xl shadow-inner">
                            <i
                                class="mdi mdi-map text-8xl text-accent dark:text-accent-light mb-6 group-hover:scale-110 transition-transform duration-300"></i>
                            <p class="text-gray-600 dark:text-gray-400 text-center text-lg">Интерактивная карта будет
                                добавлена позже</p>
                        </div>
                    </div>
                </div>

                <!-- Как добраться -->
                <div class="space-y-8">
                    <div
                        class="bg-gradient-to-br from-accent/5 to-pink-500/5 dark:from-accent-light/5 dark:to-pink-400/5 rounded-3xl p-8 border border-accent/20 dark:border-accent-light/20">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                            <i class="mdi mdi-directions text-accent dark:text-accent-light text-3xl mr-3"></i>
                            Как добраться
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-8 h-8 bg-accent dark:bg-accent-light rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-white text-sm font-bold">1</span>
                                </div>
                                <div>
                                    <p class="text-gray-700 dark:text-gray-300 text-lg">От остановки «Центральный
                                        рынок» идите направо</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-8 h-8 bg-accent dark:bg-accent-light rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-white text-sm font-bold">2</span>
                                </div>
                                <div>
                                    <p class="text-gray-700 dark:text-gray-300 text-lg">Ищите вывеску магазина «Тайга»
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div
                                    class="w-8 h-8 bg-accent dark:bg-accent-light rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <span class="text-white text-sm font-bold">3</span>
                                </div>
                                <div>
                                    <p class="text-gray-700 dark:text-gray-300 text-lg">Мы находимся в том же здании
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Фото вывески -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Фото вывески</h4>
                        <div
                            class="bg-gray-100 dark:bg-gray-700 rounded-2xl h-[200px] flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600">
                            <i class="mdi mdi-storefront text-6xl text-accent dark:text-accent-light mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Фото будет добавлено позже</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Призыв к действию -->
    <section class="py-20 bg-gradient-to-r from-accent via-pink-600 to-purple-600 relative overflow-hidden">
        <!-- Фоновые элементы -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-full h-full bg-black/10"></div>
            <div class="absolute top-10 right-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute bottom-10 left-10 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        </div>

        <div class="relative max-w-5xl mx-auto px-4 text-center text-white">
            <h2 class="text-5xl font-black mb-6">Остались вопросы?</h2>
            <p class="text-xl mb-12 opacity-90 max-w-3xl mx-auto">
                Свяжитесь с нами любым удобным способом, и мы ответим на все ваши вопросы
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-6 mb-8">
                <a href="https://t.me/zatochka_tsk"
                    class="bg-white text-accent hover:bg-gray-100 font-bold py-5 px-10 rounded-full shadow-2xl hover:shadow-3xl transform hover:-translate-y-2 transition-all duration-300 flex items-center justify-center text-lg">
                    <i class="mdi mdi-telegram mr-3 text-xl"></i>
                    Написать руководителю
                </a>
                <a href="tel:+79832335907"
                    class="border-3 border-white text-white hover:bg-white hover:text-accent font-bold py-5 px-10 rounded-full shadow-2xl hover:shadow-3xl transform hover:-translate-y-2 transition-all duration-300 flex items-center justify-center text-lg">
                    <i class="mdi mdi-phone mr-3 text-xl"></i>
                    Позвонить нам
                </a>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 max-w-md mx-auto">
                <p class="text-lg font-semibold mb-2">Для претензий и предложений:</p>
                <a href="tel:+79832335907" class="text-2xl font-bold hover:opacity-80 transition-opacity">
                    +7 (983) 233-59-07
                </a>
            </div>
        </div>
    </section>
</x-app-layout>
