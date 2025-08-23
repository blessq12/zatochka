@props([
    'contacts' => [],
    'socials' => [],
    'copyright' => '',
])

<footer class="bg-gray-900 text-white font-jost">
    <div class="max-w-7xl mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- О компании -->
            <div>
                <img src="/logo.svg" alt="Заточка ТСК" class="h-12 w-auto mb-6">
                <p class="text-gray-300 mb-6 leading-relaxed">
                    Профессиональная заточка инструментов для мастеров маникюра,
                    парикмахеров и грумеров. Более 5 лет опыта и тысячи довольных клиентов.
                </p>
                <div class="flex space-x-4">
                    <a href="https://vk.com/zatochka_tsk" target="_blank"
                        class="text-gray-400 hover:text-white transition-colors duration-300" title="VK">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 16.852h-1.744c-.66 0-.864-.525-2.052-1.713-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202-2.17-3.048-2.76-5.335-2.76-5.81 0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.678.848 2.46 2.274 4.61 2.867 4.61.22 0 .322-.102.322-.66V9.802c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.75c.373 0 .508.203.508.643v3.473c0 .373.17.508.27.508.22 0 .407-.135.813-.542 1.27-1.423 2.172-3.624 2.172-3.624.119-.254.322-.491.813-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.375 4.08-2.375 4.08-.186.305-.254.44 0 .78.186.254.796.779 1.202 1.253.745.847 1.32 1.558 1.473 2.052.17.491-.085.745-.576.745z" />
                        </svg>
                    </a>
                    <a href="https://t.me/zatochka_tsk" target="_blank"
                        class="text-gray-400 hover:text-white transition-colors duration-300" title="Telegram">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.223-.548.223l.18-2.935 5.36-4.82c.23-.23-.054-.358-.354-.128l-6.62 4.147-2.833-.886c-.617-.2-.617-.617.127-.916l11.105-4.27c.512-.2.963.127.79.916z" />
                        </svg>
                    </a>
                    <a href="https://wa.me/79832335907" target="_blank"
                        class="text-gray-400 hover:text-white transition-colors duration-300" title="WhatsApp">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Услуги -->
            <div>
                <h3 class="text-lg font-bold mb-6 text-white">Услуги</h3>
                <nav class="space-y-3">
                    <a href="{{ route('sharpening') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Заточка
                        инструментов</a>
                    <a href="{{ route('repair') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Ремонт
                        оборудования</a>
                    <a href="{{ route('delivery') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Доставка</a>
                    <a href="#"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Гарантия</a>
                </nav>
            </div>

            <!-- Информация -->
            <div>
                <h3 class="text-lg font-bold mb-6 text-white">Информация</h3>
                <nav class="space-y-3">
                    <a href="{{ route('home') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">О
                        компании</a>
                    <a href="{{ route('sharpening') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Цены</a>
                    <a href="{{ route('delivery') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Доставка</a>
                    <a href="{{ route('help') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Гарантия</a>
                    <a href="{{ route('contacts') }}"
                        class="block text-gray-300 hover:text-white transition-colors duration-300">Контакты</a>
                </nav>
            </div>

            <!-- Контакты -->
            <div>
                <h3 class="text-lg font-bold mb-6 text-white">Контакты</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <i class="mdi mdi-map-marker text-accent mt-1 mr-3"></i>
                        <div class="text-gray-300">
                            <p>Пр. Ленина 169/пер. Карповский 12</p>
                            <p class="text-sm">Вход со стороны Ленина</p>
                            <p class="text-sm">Ориентир — магазин «Тайга»</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <i class="mdi mdi-phone text-accent mr-3"></i>
                        <a href="tel:+79832335907"
                            class="text-gray-300 hover:text-white transition-colors duration-300">
                            +7 (983) 233-59-07
                        </a>
                    </div>
                    <div class="flex items-center">
                        <i class="mdi mdi-email text-accent mr-3"></i>
                        <a href="mailto:zatochka.tsk@yandex.ru"
                            class="text-gray-300 hover:text-white transition-colors duration-300">
                            zatochka.tsk@yandex.ru
                        </a>
                    </div>
                    <div class="flex items-center">
                        <i class="mdi mdi-clock text-accent mr-3"></i>
                        <span class="text-gray-300">Пн-Вс: 9:00 - 20:00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Нижняя часть футера -->
        <div class="border-t border-gray-800 mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">&copy; 2024 Заточка ТСК. Все права защищены.</p>
                <div class="mt-4 md:mt-0 flex space-x-6 text-sm">
                    <a href="{{ route('privacy-policy') }}"
                        class="text-gray-400 hover:text-white transition-colors duration-300">Политика
                        конфиденциальности</a>
                    <a href="{{ route('terms-of-service') }}"
                        class="text-gray-400 hover:text-white transition-colors duration-300">Пользовательское
                        соглашение</a>
                    <a href="{{ route('help') }}"
                        class="text-gray-400 hover:text-white transition-colors duration-300">Помощь</a>
                </div>
            </div>
        </div>
    </div>
</footer>
