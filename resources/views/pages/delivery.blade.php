<x-app-layout title="Условия доставки">
    <!-- Hero секция -->
    <x-page-hero title="Условия <span class='text-accent'>доставки</span>"
        description="Удобная доставка ваших инструментов для заточки и ремонта. Бесплатная доставка при заказе от определенного количества инструментов."
        :breadcrumbs="[['name' => 'Условия доставки', 'href' => route('delivery')]]" />

    <!-- Основные условия -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">Стоимость доставки</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="feature-card hover:shadow-lg transition-all p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center mr-4">
                            <i class="mdi mdi-check-circle text-green-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold dark:text-white">Бесплатная доставка</h3>
                    </div>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="mdi mdi-check-circle-outline text-green-500 mt-1 mr-3"></i>
                            <span class="dark:text-gray-300">От 6 маникюрных инструментов</span>
                        </li>
                        <li class="flex items-start">
                            <i class="mdi mdi-check-circle-outline text-green-500 mt-1 mr-3"></i>
                            <span class="dark:text-gray-300">От 3 парикмахерских/грумерских/барберских
                                инструментов</span>
                        </li>
                        <li class="flex items-start">
                            <i class="mdi mdi-check-circle-outline text-green-500 mt-1 mr-3"></i>
                            <span class="dark:text-gray-300">Любой аппарат в ремонт</span>
                        </li>
                    </ul>
                </div>

                <div class="feature-card hover:shadow-lg transition-all p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                            <i class="mdi mdi-currency-rub text-accent text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold dark:text-white">Платная доставка</h3>
                    </div>
                    <div class="flex items-center bg-accent/5 p-4 rounded-lg">
                        <i class="mdi mdi-information-outline text-accent text-3xl mr-4"></i>
                        <div>
                            <p class="font-bold dark:text-white">150 ₽ в одну сторону</p>
                            <p class="text-gray-600 dark:text-gray-400">Если количество инструментов меньше указанного
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300">Оплата доставки производится курьеру наличными или
                            переводом на карту</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- График работы -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">График доставки</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="feature-card hover:shadow-lg transition-all p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                            <i class="mdi mdi-calendar-check text-accent text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold dark:text-white">Рабочие дни</h3>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="mdi mdi-clock-time-four-outline text-accent mr-3"></i>
                            <span class="dark:text-gray-300">Понедельник: 13:00 - 17:00</span>
                        </li>
                        <li class="flex items-center">
                            <i class="mdi mdi-clock-time-four-outline text-accent mr-3"></i>
                            <span class="dark:text-gray-300">Вторник: 13:00 - 17:00</span>
                        </li>
                        <li class="flex items-center">
                            <i class="mdi mdi-clock-time-four-outline text-accent mr-3"></i>
                            <span class="dark:text-gray-300">Среда: 13:00 - 17:00</span>
                        </li>
                        <li class="flex items-center">
                            <i class="mdi mdi-clock-time-four-outline text-accent mr-3"></i>
                            <span class="dark:text-gray-300">Пятница: 13:00 - 17:00</span>
                        </li>
                        <li class="flex items-center">
                            <i class="mdi mdi-clock-time-four-outline text-accent mr-3"></i>
                            <span class="dark:text-gray-300">Суббота: 13:00 - 17:00</span>
                        </li>
                    </ul>
                </div>

                <div class="feature-card hover:shadow-lg transition-all p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                            <i class="mdi mdi-calendar-remove text-accent text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold dark:text-white">Выходные дни</h3>
                    </div>
                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 p-6 rounded-lg mb-4">
                        <i class="mdi mdi-calendar-weekend text-accent text-3xl mr-4"></i>
                        <div>
                            <p class="font-bold text-lg dark:text-white">Четверг</p>
                        </div>
                    </div>
                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 p-6 rounded-lg">
                        <i class="mdi mdi-calendar-weekend text-accent text-3xl mr-4"></i>
                        <div>
                            <p class="font-bold text-lg dark:text-white">Воскресенье</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Форма заказа -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">Заказать доставку</h2>
            <delivery-order-form></delivery-order-form>
        </div>
    </section>

    <!-- Преимущества -->
    <section class="py-16 bg-accent text-white">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-black mb-10">Преимущества нашей доставки</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white/10 p-6 rounded-xl backdrop-blur-sm">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="mdi mdi-clock-fast text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Быстрая доставка</h3>
                    <p class="opacity-90">Доставляем в течение рабочего дня по предварительной договоренности</p>
                </div>

                <div class="bg-white/10 p-6 rounded-xl backdrop-blur-sm">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="mdi mdi-package-variant-closed text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Безопасная упаковка</h3>
                    <p class="opacity-90">Используем специальную упаковку для защиты ваших инструментов</p>
                </div>

                <div class="bg-white/10 p-6 rounded-xl backdrop-blur-sm">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="mdi mdi-shield-check text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Гарантия качества</h3>
                    <p class="opacity-90">Несем ответственность за сохранность ваших инструментов</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
