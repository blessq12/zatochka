<x-app-layout title="ЗАТОЧКА.ТСК - профессиональная заточка инструментов"
    description="Заточка маникюрных, парикмахерских, грумерских инструментов и ремонт оборудования. Более 5 лет опыта и более 30 000 восстановленных инструментов"
    keywords="заточка инструментов, маникюр, парикмахерские ножницы, груминг, ремонт оборудования"
    canonical="https://zatochka.tsk">

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Личный кабинет
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Управляйте своим профилем, заказами и настройками
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Основная информация -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Карточка профиля -->
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Профиль
                        </h2>
                        <client-auth></client-auth>
                    </div>

                    <!-- Заказы -->
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Мои заказы
                        </h2>
                        <div class="text-center py-8">
                            <i class="mdi mdi-clipboard-list text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">
                                Здесь будут отображаться ваши заказы
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                                Функция находится в разработке
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="space-y-6">
                    <!-- Telegram Бот -->
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Telegram Бот
                        </h2>
                        <telegram-bot-info></telegram-bot-info>
                    </div>

                    <!-- Быстрые действия -->
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Быстрые действия
                        </h2>
                        <div class="space-y-3">
                            <a href="{{ route('sharpening') }}"
                                class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="mdi mdi-tools text-accent text-xl mr-3"></i>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">Заточка инструмента</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Оставить заявку</div>
                                </div>
                            </a>

                            <a href="{{ route('repair') }}"
                                class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="mdi mdi-wrench text-accent text-xl mr-3"></i>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">Ремонт инструмента</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Оставить заявку</div>
                                </div>
                            </a>

                            <a href="{{ route('delivery') }}"
                                class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="mdi mdi-truck text-accent text-xl mr-3"></i>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">Доставка</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Узнать условия</div>
                                </div>
                            </a>

                            <a href="{{ route('contacts') }}"
                                class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="mdi mdi-phone text-accent text-xl mr-3"></i>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">Контакты</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Связаться с нами</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Полезная информация -->
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Полезная информация
                        </h2>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <i class="mdi mdi-information text-blue-500 mt-1 mr-3"></i>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Время работы:</strong><br>
                                    Пн-Пт: 9:00 - 18:00<br>
                                    Сб: 9:00 - 15:00<br>
                                    Вс: Выходной
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="mdi mdi-clock text-green-500 mt-1 mr-3"></i>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Сроки выполнения:</strong><br>
                                    Заточка: 1-3 дня<br>
                                    Ремонт: 3-7 дней
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
