<x-app-layout title="Заточка инструментов">
    <!-- Hero секция -->
    <x-page-hero title="Заточка <span class='text-accent'>инструментов</span>"
        description="Профессиональная заточка маникюрных, парикмахерских и грумерских инструментов. Восстанавливаем остроту и работоспособность ваших инструментов."
        :breadcrumbs="[['name' => 'Заточка инструментов', 'href' => route('sharpening')]]" />

    <!-- Прайс -->
    <section class="py-24 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-4xl font-bold mb-12 dark:text-white text-center font-jost">Прайс на заточку</h1>

            <!-- Маникюрные инструменты -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6 dark:text-white flex items-center font-jost">
                    <i class="mdi mdi-hand-front text-accent mr-3"></i>
                    Маникюрные инструменты
                </h2>
                <div class="feature-card hover:shadow-lg transition-all p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-4 dark:text-white font-semibold">Услуга</th>
                                    <th class="text-right py-4 dark:text-white font-semibold">Цена</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Кусачки</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 500 ₽</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Ножницы</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 600 ₽</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Щипчики</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 400 ₽</td>
                                </tr>
                                <tr>
                                    <td class="py-3 dark:text-gray-300">Твизеры</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 350 ₽</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Парикмахерские инструменты -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6 dark:text-white flex items-center font-jost">
                    <i class="mdi mdi-content-cut text-accent mr-3"></i>
                    Парикмахерские инструменты
                </h2>
                <div class="feature-card hover:shadow-lg transition-all p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-4 dark:text-white font-semibold">Услуга</th>
                                    <th class="text-right py-4 dark:text-white font-semibold">Цена</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Прямые ножницы</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 800 ₽</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Филировочные ножницы</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 1000 ₽</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Конвекс ножницы</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 1200 ₽</td>
                                </tr>
                                <tr>
                                    <td class="py-3 dark:text-gray-300">Машинки для стрижки</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 1500 ₽</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Груминг инструменты -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6 dark:text-white flex items-center font-jost">
                    <i class="mdi mdi-dog text-accent mr-3"></i>
                    Груминг инструменты
                </h2>
                <div class="feature-card hover:shadow-lg transition-all p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-4 dark:text-white font-semibold">Услуга</th>
                                    <th class="text-right py-4 dark:text-white font-semibold">Цена</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Ножницы для груминга</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 700 ₽</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 dark:text-gray-300">Машинки для стрижки шерсти</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 900 ₽</td>
                                </tr>
                                <tr>
                                    <td class="py-3 dark:text-gray-300">Триммеры</td>
                                    <td class="text-right dark:text-gray-300 font-semibold text-accent">от 600 ₽</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Дополнительные услуги -->
            <div>
                <h2 class="text-2xl font-bold mb-6 dark:text-white flex items-center font-jost">
                    <i class="mdi mdi-plus-circle text-accent mr-3"></i>
                    Дополнительные услуги
                </h2>
                <div class="feature-card hover:shadow-lg transition-all p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 bg-accent/5 rounded-lg">
                            <i class="mdi mdi-shield-check text-accent text-2xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold dark:text-white">Гарантия качества</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">30 дней на все работы</p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-accent/5 rounded-lg">
                            <i class="mdi mdi-clock-fast text-accent text-2xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold dark:text-white">Срочная заточка</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">+50% к стоимости</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Форма заказа -->
    <section class="py-24 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center dark:text-white">Заказать заточку</h2>
            <sharpening-order-form></sharpening-order-form>
        </div>
    </section>
</x-app-layout>
