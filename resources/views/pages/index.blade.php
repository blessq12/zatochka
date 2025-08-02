<x-app-layout title="ЗАТОЧКА.ТСК - профессиональная заточка инструментов"
    description="Заточка маникюрных, парикмахерских, грумерских инструментов и ремонт оборудования. Более 5 лет опыта и более 30 000 восстановленных инструментов"
    keywords="заточка инструментов, маникюр, парикмахерские ножницы, груминг, ремонт оборудования"
    canonical="https://zatochka.tsk">

    <!-- Форма заказа (вверху страницы) -->
    <section class="hero-card">
        <div class="pt-32">
            <div class="max-w-7xl mx-auto block md:flex items-center space-x-4 px-4">
                <div class="flex-1 mb-12 md:mb-0">
                    <h6 class="text-md text-start mb-1 font-bold text-accent/60">ОСТРЫЕ ИНСТРУМЕНТЫ ЗА 2 ДНЯ
                    </h6>
                    <h1 class="text-4xl md:text-7xl font-black text-start text-primary">ЗАТОЧКА.ТСК</h1>
                    <div class="text-sm max-w-md text-start mb-6 text-gray-700 font-bold">
                        <p class="mb-4">Более 5 лет затачиваем и ремонтируем инструменты для мастеров маникюра,
                            парикмахеров, грумеров и лешмейкеров.</p>
                        <ul class="list-disc pl-6 mb-4 text-sm">
                            <li>Восстановили 30 000+ инструментов: ножницы, кусачки, пинцеты, машинки</li>
                            <li>Бесплатная доставка при заказе от 6 маникюрных или 3 парикмахерских/грумерских
                                инструментов
                            </li>
                        </ul>
                        <p class="mb-0">
                            Гарантия:
                        </p>
                        <div class="flex gap-4">
                            <div class="px-4 py-2 bg-accent/5 rounded-lg text-accent/60">
                                <span class="font-bold">на заточку:</span> 3 дня
                            </div>
                            <div class="px-4 py-2 bg-accent/5 rounded-lg text-accent/60">
                                <span class="font-bold">на ремонт:</span> 6 месяцев
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#order-form" class="btn-primary flex items-center justify-center">
                            <i class="mdi mdi-scissors-cutting mr-2"></i> Заказать заточку
                        </a>
                        <a href="{{ route('repair') }}" class="btn-primary flex items-center justify-center">
                            <i class="mdi mdi-tools mr-2"></i> Заказать ремонт
                        </a>
                    </div>
                </div>
                <div class="flex-1">
                    <hero-form header="Заказать доставку"
                        description="Ознакомлен с условиями доставки: ≥6 маникюрных или ≥3 парикмахерских/грумерских/барберских инструментов для бесплатной доставки"></hero-form>
                </div>
            </div>
            <div class="flex justify-center mt-24">
                <div class="block text-center cursor-pointer" onclick="alert('Скролл ниже')">
                    <p class="text-gray-700 font-bold" style="margin-bottom: 0px;">Узнай подробнее</p>
                    <i class="mdi mdi-chevron-down text-4xl text-accent animate-bounce"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Навигация -->
    <section class="py-16 bg-gray-50 [box-shadow:inset_0_0_30px_0_rgba(0,0,0,0.1)]">
        <div class="max-w-7xl mx-auto px-4 py-24">
            <h2 class="section-title text-3xl font-bold text-center mb-12">Навигация</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('sharpening') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-knife text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2">Заточка</h3>
                    <p class="text-gray-600 text-center">Прайс на заточку инструментов</p>
                </a>
                <a href="{{ route('repair') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-tools text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2">Ремонт</h3>
                    <p class="text-gray-600 text-center">Прайс на ремонт оборудования</p>
                </a>
                <a href="{{ route('delivery') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-truck-delivery text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2">Доставка</h3>
                    <p class="text-gray-600 text-center">Условия доставки</p>
                </a>
                <a href="{{ route('contacts') }}" class="feature-card hover:shadow-lg transition-all">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-accent/10 flex items-center justify-center">
                            <i class="mdi mdi-map-marker text-3xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-center mb-2">Куда везти</h3>
                    <p class="text-gray-600 text-center">Адрес и контакты</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Наши услуги -->
    <section class="py-0">
        <div class="py-24">
            <div class="max-w-7xl mx-auto px-4 ">
                <h2 class="section-title text-3xl font-bold text-center mb-12">Наши услуги</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="feature-card hover:shadow-lg transition-all">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mr-4">
                                <i class="mdi mdi-knife text-2xl text-accent"></i>
                            </div>
                            <h3 class="text-xl font-bold">Заточка инструментов</h3>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span>Маникюр и подология: ножницы, кусачки, твизеры, пушеры</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span>Парикмахеры/барберы: прямые, конвекс, филировочные ножницы, машинки</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span>Грумеры: ножницы, машинки для стрижки шерсти</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span>Лешмейкеры/бровисты: пинцеты</span>
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
                            <h3 class="text-xl font-bold">Ремонт оборудования</h3>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span>Маникюрное и педикюрное оборудование</span>
                            </li>
                            <li class="flex items-start">
                                <i class="mdi mdi-check-circle text-accent mt-1 mr-2"></i>
                                <span>Парикмахерское оборудование</span>
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
    <section class="py-16 bg-gray-50 [box-shadow:inset_0_0_30px_0_rgba(0,0,0,0.1)]">

        <div class="py-24">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="section-title text-3xl font-bold text-center mb-12">Как мы работаем</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="process-step">
                        <div class="process-number">1</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-magnify text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center">Диагностика</h3>
                            <p class="text-gray-600 text-center">Осмотр инструментов, сбор жалоб, определение работ</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">2</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-handshake text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center">Согласование</h3>
                            <p class="text-gray-600 text-center">Согласование ремонта аппаратов</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">3</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-tools text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center">Выполнение</h3>
                            <p class="text-gray-600 text-center">Работа по современным протоколам с профессиональным
                                оборудованием</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">4</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-check-decagram text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center">Контроль качества</h3>
                            <p class="text-gray-600 text-center">Тестирование заточки (претензии в течение 3 дней),
                                прокатка аппарата на холостом ходу</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="process-number">5</div>
                        <div class="feature-card h-full">
                            <div class="flex justify-center mb-4">
                                <i class="mdi mdi-package-variant-closed text-4xl text-accent"></i>
                            </div>
                            <h3 class="font-bold text-xl mb-3 text-center">Упаковка</h3>
                            <p class="text-gray-600 text-center">Тщательная упаковка для сохранности при
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
                            <h3 class="font-bold text-xl mb-3 text-center">Доставка</h3>
                            <p class="text-gray-600 text-center">Бережная доставка, курьер проинструктирован</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Частые вопросы -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 py-24">
            <h2 class="section-title text-3xl font-bold text-center mb-12">Частые вопросы</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i class="mdi mdi-clock-outline text-accent mr-3"></i>Как долго
                            делается заточка?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>Стандарт: 2 рабочих дня (пн, вт, ср, пт, сб). При высокой загрузке — согласование сроков. <a
                                href="{{ route('delivery') }}" class="text-accent">График</a>.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i class="mdi mdi-clock-outline text-accent mr-3"></i>Как долго
                            ремонтируется аппарат?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>Стандарт: 2 рабочих дня. Сложный ремонт/отсутствие запчастей — согласование сроков, 100%
                            предоплата за детали. <a href="{{ route('delivery') }}" class="text-accent">График</a>.
                        </p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i class="mdi mdi-shield-check text-accent mr-3"></i>Есть
                            гарантия на заточку?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>Претензии в течение 3 дней (при сохранности геометрии, без механического воздействия). Связь
                            через <a href="https://t.me/+79832335907" class="text-accent">Telegram</a> или <a
                                href="https://t.me/+79832335907" class="text-accent">кнопка в боте</a>.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i class="mdi mdi-shield-check text-accent mr-3"></i>Есть
                            гарантия на ремонт?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>6 месяцев, при условии профилактической чистки через 4–5 месяцев.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i class="mdi mdi-truck-delivery text-accent mr-3"></i>Как
                            происходит доставка?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>Заявка до 13:00 — курьер в тот же день, после — на следующий рабочий день. Клиент должен быть
                            на связи, иначе повторный вызов платный. Курьер бережно доставляет.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i
                                class="mdi mdi-credit-card-outline text-accent mr-3"></i>Какие способы оплаты?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>Наличные, QR-код, перевод по номеру телефона, онлайн-оплата картой (после настройки кассы).
                        </p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3 class="flex items-center"><i class="mdi mdi-tools text-accent mr-3"></i>Что делать, если
                            инструмент сильно поврежден?</h3>
                        <div class="faq-icon"></div>
                    </div>
                    <div class="faq-answer">
                        <p>95% инструментов можно восстановить. Присылайте фото в <a href="https://t.me/+79832335907"
                                class="text-accent">бот</a> для консультации.</p>
                        <!-- Добавить карусель с фото до/после -->
                    </div>
                </div>
            </div>
            <div class="text-center mt-12">
                <a href="https://t.me/+79832335907" class="btn-primary inline-flex items-center">
                    <i class="mdi mdi-telegram mr-2"></i>
                    Написать руководителю
                </a>
            </div>
        </div>
    </section>

    <!-- Отзывы -->
    <section class="bg-gray-50 [box-shadow:inset_0_0_30px_rgba(0,0,0,0.1)]">
        <div class="py-24">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="section-title text-3xl font-bold text-center mb-12">Отзывы</h2>
                <div class="2gis-reviews">
                    <!-- Интеграция с 2ГИС для подтягивания отзывов -->
                    <p class="text-center text-gray-600">Здесь будут отображаться отзывы из 2ГИС</p>
                </div>
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
