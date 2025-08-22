<x-app-layout title="Контакты">
    <!-- Hero секция -->
    <x-page-hero 
        title="Наши <span class='text-accent'>контакты</span>"
        description="Мы всегда рады помочь вам с заточкой и ремонтом инструментов. Свяжитесь с нами любым удобным способом."
        :breadcrumbs="[
            ['name' => 'Контакты', 'href' => route('contacts')]
        ]"
    />

    <!-- Основная информация -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="contact-card hover:shadow-xl transition-all">
                    <div class="contact-icon">
                        <i class="mdi mdi-phone text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-4 dark:text-white">Телефон</h3>
                    <p class="text-lg mb-2">
                        <a href="tel:+79832335907" class="contact-link flex items-center justify-center">
                            <i class="mdi mdi-phone-in-talk mr-2"></i>
                            +7 (983) 233-59-07
                        </a>
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 text-center">Максим</p>
                </div>

                <div class="contact-card hover:shadow-xl transition-all">
                    <div class="contact-icon">
                        <i class="mdi mdi-email text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-4 dark:text-white">Email</h3>
                    <p class="text-lg">
                        <a href="mailto:zatochka.tsk@yandex.ru" class="contact-link flex items-center justify-center">
                            <i class="mdi mdi-email-outline mr-2"></i>
                            zatochka.tsk@yandex.ru
                        </a>
                    </p>
                </div>

                <div class="contact-card hover:shadow-xl transition-all">
                    <div class="contact-icon">
                        <i class="mdi mdi-map-marker text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-4 dark:text-white">Адрес</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-center">
                        <i class="mdi mdi-map-marker-radius mb-2 text-accent text-2xl"></i><br>
                        Пр. Ленина 169/пер. Карповский 12<br>
                        Вход со стороны Ленина<br>
                        Ориентир — магазин «Тайга»
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Карта и фото -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-12 dark:text-white">Как нас найти</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Карта -->
                <div class="map-container shadow-lg">
                    <div class="w-full h-full rounded-lg overflow-hidden">
                        <!-- Здесь будет карта 2GIS -->
                        <div
                            class="w-full h-full flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-700">
                            <i class="mdi mdi-map text-6xl text-accent mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">Интерактивная карта будет добавлена позже</p>
                        </div>
                    </div>
                </div>

                <!-- Фото -->
                <div class="space-y-6">
                    <h3 class="font-bold text-2xl flex items-center dark:text-white">
                        <i class="mdi mdi-directions mr-3 text-accent"></i>
                        Как добраться
                    </h3>
                    <div class="feature-card p-6">
                        <p class="text-gray-700 dark:text-gray-300 mb-6 text-lg">
                            От остановки «Центральный рынок» идите направо до вывески. Мы находимся в здании с магазином
                            «Тайга».
                        </p>
                        <div
                            class="bg-gray-100 dark:bg-gray-700 rounded-xl h-[300px] flex flex-col items-center justify-center shadow-inner">
                            <i class="mdi mdi-storefront text-6xl text-accent mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">Фото вывески будет добавлено позже</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Социальные сети -->
    <section class="py-16 bg-white">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="section-title text-3xl font-bold text-center mb-12">Мы в социальных сетях</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <a href="#" class="feature-card text-center hover:shadow-lg transition-all p-6">
                    <div class="w-16 h-16 bg-[#0088cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="mdi mdi-telegram text-[#0088cc] text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Telegram-канал</h3>
                    <p class="text-gray-600">Подписывайтесь на наш канал для получения новостей и акций</p>
                </a>

                <a href="#" class="feature-card text-center hover:shadow-lg transition-all p-6">
                    <div class="w-16 h-16 bg-[#4C75A3]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="mdi mdi-vk text-[#4C75A3] text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">ВКонтакте</h3>
                    <p class="text-gray-600">Присоединяйтесь к нашей группе ВКонтакте</p>
                </a>

                <a href="https://wa.me/79832335907" class="feature-card text-center hover:shadow-lg transition-all p-6">
                    <div class="w-16 h-16 bg-[#25D366]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="mdi mdi-whatsapp text-[#25D366] text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">WhatsApp</h3>
                    <p class="text-gray-600">Напишите нам в WhatsApp для быстрой связи</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Кнопка для связи -->
    <section class="py-16 bg-accent text-white">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-black mb-6">Остались вопросы?</h2>
            <p class="text-xl mb-8 opacity-90">Свяжитесь с нами любым удобным способом, и мы ответим на все ваши вопросы
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="https://t.me/zatochka_tsk" class="btn-white flex items-center justify-center">
                    <i class="mdi mdi-send mr-2"></i>
                    Написать руководителю
                </a>
                <a href="tel:+79832335907" class="btn-outline-white flex items-center justify-center">
                    <i class="mdi mdi-phone mr-2"></i>
                    Позвонить нам
                </a>
            </div>

            <p class="mt-8 opacity-80">
                Для претензий и предложений: +7 (983) 233-59-07
            </p>
        </div>
    </section>
</x-app-layout>
