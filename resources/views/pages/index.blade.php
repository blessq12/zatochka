@php
    $routes = [
        'sharpening' => route('sharpening'),
        'repair' => route('repair'),
        'delivery' => route('delivery'),
        'contacts' => route('contacts'),
    ];
@endphp

<x-app-layout title="ЗАТОЧКА.ТСК - профессиональная заточка инструментов"
    description="Заточка маникюрных, парикмахерских, грумерских инструментов и ремонт оборудования. Более 5 лет опыта и более 30 000 восстановленных инструментов"
    keywords="заточка инструментов, маникюр, парикмахерские ножницы, груминг, ремонт оборудования"
    canonical="https://zatochka.tsk">

    <!-- Hero Banner с анимациями -->
    <hero-banner :routes='@json($routes)'></hero-banner>

    <!-- Навигация -->
    <section
        class="py-16 bg-gray-50 dark:bg-gray-800 [box-shadow:inset_0_0_30px_0_rgba(0,0,0,0.1)] dark:[box-shadow:inset_0_0_30px_0_rgba(255,255,255,0.05)]">
        <div class="max-w-7xl mx-auto px-4 py-24">
            <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">Навигация</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('sharpening') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-knife text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2 dark:text-white">Заточка</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-center">Прайс на заточку инструментов</p>
                </a>
                <a href="{{ route('repair') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-tools text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2 dark:text-white">Ремонт</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-center">Прайс на ремонт оборудования</p>
                </a>
                <a href="{{ route('delivery') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-truck-delivery text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2 dark:text-white">Доставка</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-center">Условия доставки</p>
                </a>
                <a href="{{ route('contacts') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-map-marker text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2 dark:text-white">Куда везти</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-center">Адрес и контакты</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Наши услуги -->
    <section class="py-0 dark:bg-gray-900">
        <div class="py-24">
            <div class="max-w-7xl mx-auto px-4 ">
                <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">Наши услуги</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="feature-card hover:shadow-lg transition-all">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                                <i class="mdi mdi-knife text-2xl text-accent"></i>
                            </div>
                            <h3 class="text-xl font-bold dark:text-white">Заточка инструментов</h3>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span class="dark:text-gray-300">Маникюр и подология: ножницы, кусачки, твизеры,
                                    пушеры</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span class="dark:text-gray-300">Парикмахеры/барберы: прямые, конвекс, филировочные
                                    ножницы, машинки</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span class="dark:text-gray-300">Грумеры: ножницы, машинки для стрижки шерсти</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span class="dark:text-gray-300">Лешмейкеры/бровисты: пинцеты</span>
                            </li>
                        </ul>
                        <a href="{{ route('sharpening') }}" class="btn-primary mt-4 inline-flex items-center">Узнать
                            цены</a>
                    </div>
                    <div class="feature-card hover:shadow-lg transition-all">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                                <i class="mdi mdi-tools text-2xl text-accent"></i>
                            </div>
                            <h3 class="text-xl font-bold dark:text-white">Ремонт оборудования</h3>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span class="dark:text-gray-300">Маникюрное и педикюрное оборудование</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span class="dark:text-gray-300">Парикмахерское оборудование</span>
                            </li>
                        </ul>
                        <a href="{{ route('repair') }}" class="btn-primary mt-4 inline-flex items-center">Узнать
                            цены</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Как мы работаем -->
    <section
        class="py-16 bg-gray-50 dark:bg-gray-800 [box-shadow:inset_0_0_30px_0_rgba(0,0,0,0.1)] dark:[box-shadow:inset_0_0_30px_0_rgba(255,255,255,0.05)]">

        <div class="py-24">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">Как мы работаем</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="process-step">
                        <div class="process-number">1</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-magnify text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center dark:text-white">Диагностика</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Осмотр инструментов, сбор жалоб,
                                определение работ</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">2</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-handshake text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center dark:text-white">Согласование</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Согласование ремонта аппаратов</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">3</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-tools text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center dark:text-white">Выполнение</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Работа по современным протоколам с
                                профессиональным
                                оборудованием</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">4</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-check-decagram text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center dark:text-white">Контроль качества</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Тестирование заточки (претензии в
                                течение 3 дней),
                                прокатка аппарата на холостом ходу</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">5</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-package-variant-closed text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center dark:text-white">Упаковка</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Тщательная упаковка для сохранности
                                при
                                транспортировке
                            </p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">6</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-truck-delivery text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center dark:text-white">Доставка</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">Бережная доставка, курьер
                                проинструктирован</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section
        class="py-16 bg-gray-50 dark:bg-gray-800 [box-shadow:inset_0_0_30px_0_rgba(0,0,0,0.1)] dark:[box-shadow:inset_0_0_30px_0_rgba(255,255,255,0.05)]">
        <faq></faq>
    </section>

    <!-- Отзывы -->
    <section
        class="bg-gray-50 dark:bg-gray-800 [box-shadow:inset_0_0_30px_rgba(0,0,0,0.1)] dark:[box-shadow:inset_0_0_30px_rgba(255,255,255,0.05)]">
        <div class="py-24">
            <div class="max-w-7xl mx-auto px-4">
                <reviews entity-type="App\Models\Company" :entity-id="1" type="testimonial">
                </reviews>
            </div>
        </div>
    </section>

    <!-- Призыв к действию -->
    <section class="py-16 bg-accent text-white">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-black mb-6">Готовы вернуть инструментам остроту?</h2>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#order-form" class="btn-white flex items-center justify-center">
                    <i class="mdi mdi-scissors-cutting mr-2"></i>
                    Заказать доставку
                </a>
                <a href="{{ route('contacts') }}" class="btn-outline-white flex items-center justify-center">
                    <i class="mdi mdi-phone mr-2"></i>
                    Связаться с нами
                </a>
            </div>
        </div>
    </section>

</x-app-layout>
