<x-app-layout title="Ремонт оборудования">
    <!-- Hero секция -->
    <x-page-hero title="Ремонт <span class='text-accent'>оборудования</span>"
        description="Профессиональный ремонт маникюрного, парикмахерского и грумерского оборудования. Диагностика, ремонт и обслуживание вашей техники."
        :breadcrumbs="[['name' => 'Ремонт оборудования', 'href' => route('repair')]]" />

    <!-- Прайс -->
    <section class="py-24 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-4xl font-bold mb-12 dark:text-white text-center">Ремонт оборудования</h1>

            <div class="feature-card hover:shadow-lg transition-all p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-4 dark:text-white font-semibold">Услуга</th>
                                <th class="text-right py-4 dark:text-white font-semibold">Цена</th>
                                <th class="text-right py-4 dark:text-white font-semibold">Срок</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 dark:text-gray-300">Диагностика оборудования</td>
                                <td class="text-right dark:text-gray-300 font-semibold text-green-500">Бесплатно</td>
                                <td class="text-right dark:text-gray-300">30 мин</td>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 dark:text-gray-300">Ремонт машинок для стрижки</td>
                                <td class="text-right dark:text-gray-300 font-semibold text-accent">от 1000 ₽</td>
                                <td class="text-right dark:text-gray-300">1-3 дня</td>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 dark:text-gray-300">Ремонт фенов</td>
                                <td class="text-right dark:text-gray-300 font-semibold text-accent">от 800 ₽</td>
                                <td class="text-right dark:text-gray-300">1-2 дня</td>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 dark:text-gray-300">Ремонт электрических ножниц</td>
                                <td class="text-right dark:text-gray-300 font-semibold text-accent">от 1500 ₽</td>
                                <td class="text-right dark:text-gray-300">2-4 дня</td>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 dark:text-gray-300">Ремонт триммеров</td>
                                <td class="text-right dark:text-gray-300 font-semibold text-accent">от 600 ₽</td>
                                <td class="text-right dark:text-gray-300">1-2 дня</td>
                            </tr>
                            <tr>
                                <td class="py-3 dark:text-gray-300">Ремонт ультразвуковых ванн</td>
                                <td class="text-right dark:text-gray-300 font-semibold text-accent">от 2000 ₽</td>
                                <td class="text-right dark:text-gray-300">3-5 дней</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Дополнительные услуги -->
            <div class="mt-12">
                <h2 class="text-2xl font-bold mb-6 dark:text-white flex items-center">
                    <i class="mdi mdi-wrench text-accent mr-3"></i>
                    Дополнительные услуги
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="feature-card hover:shadow-lg transition-all p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                                <i class="mdi mdi-shield-check text-accent text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold dark:text-white">Гарантия</h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">90 дней гарантии на все виды ремонта</p>
                    </div>

                    <div class="feature-card hover:shadow-lg transition-all p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                                <i class="mdi mdi-clock-fast text-accent text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold dark:text-white">Срочный ремонт</h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">Срочный ремонт в течение 24 часов</p>
                    </div>

                    <div class="feature-card hover:shadow-lg transition-all p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                                <i class="mdi mdi-truck text-accent text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold dark:text-white">Бесплатная доставка</h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">Доставка в обе стороны бесплатная</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Форма заказа -->
    <section class="py-24 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-8 text-center dark:text-white">Заказать ремонт</h2>
            <universal-order-form :initial-service-type="'repair'"></universal-order-form>
        </div>
    </section>
</x-app-layout>
