<x-layouts.app title="Помощь">
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-8 dark:text-gray-100">Помощь</h1>
            <p class="text-xl sm:text-2xl text-gray-700 mb-12 dark:text-gray-300">
                Часто задаваемые вопросы и полезная информация о наших услугах.
            </p>

            <div class="space-y-8">
                <div
                    class="bg-gray-50/80 backdrop-blur-lg rounded-2xl p-8 border border-gray-200/30 dark:bg-gray-700/80 dark:border-gray-600/20 hover:shadow-2xl hover:-translate-y-2 transform transition-all duration-500 ease-out">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4 dark:text-gray-100">Как заказать услугу?</h2>
                    <p class="text-lg text-gray-700 dark:text-gray-300">Свяжитесь с нами по телефону или через форму
                        обратной связи. Мы обсудим детали и согласуем время.</p>
                </div>

                <div
                    class="bg-gray-50/80 backdrop-blur-lg rounded-2xl p-8 border border-gray-200/30 dark:bg-gray-700/80 dark:border-gray-600/20 hover:shadow-2xl hover:-translate-y-2 transform transition-all duration-500 ease-out">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4 dark:text-gray-100">Сколько времени занимает
                        заточка?</h2>
                    <p class="text-lg text-gray-700 dark:text-gray-300">Обычно заточка занимает 1-3 дня в зависимости от
                        сложности и загруженности.</p>
                </div>

                <div
                    class="bg-gray-50/80 backdrop-blur-lg rounded-2xl p-8 border border-gray-200/30 dark:bg-gray-700/80 dark:border-gray-600/20 hover:shadow-2xl hover:-translate-y-2 transform transition-all duration-500 ease-out">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4 dark:text-gray-100">Есть ли гарантия на работу?
                    </h2>
                    <p class="text-lg text-gray-700 dark:text-gray-300">Да, мы предоставляем гарантию на все виды работ
                        в течение 30 дней.</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">Не нашли ответ на свой вопрос?</p>
                <a href="{{ route('contacts') }}"
                    class="bg-blue-600/90 backdrop-blur-md hover:bg-blue-700/90 text-white px-10 py-5 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90">
                    Связаться с нами
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
