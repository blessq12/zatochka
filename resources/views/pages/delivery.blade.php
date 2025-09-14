<x-layouts.app title="Доставка">
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-8 dark:text-gray-100">Доставка</h1>
            <p class="text-xl sm:text-2xl text-gray-700 mb-12 dark:text-gray-300">
                Удобная доставка инструментов по городу. Мы заберем ваши инструменты и привезем их обратно после
                обработки.
            </p>

            <div
                class="bg-yellow-50/80 backdrop-blur-lg rounded-2xl p-10 mb-12 border border-yellow-200/30 dark:bg-yellow-900/30 dark:border-yellow-800/20">
                <h2 class="text-2xl font-semibold text-yellow-700 mb-6 dark:text-yellow-400">Условия доставки:</h2>
                <ul class="space-y-4 text-lg text-yellow-600 dark:text-yellow-300">
                    <li>• Бесплатная доставка по городу</li>
                    <li>• Забор и возврат инструментов</li>
                    <li>• Безопасная упаковка</li>
                    <li>• Отслеживание статуса заказа</li>
                    <li>• Удобное время доставки</li>
                </ul>
            </div>

            <div class="text-center">
                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">Страница в разработке...</p>
                <a href="{{ route('contacts') }}"
                    class="bg-yellow-600/90 backdrop-blur-md hover:bg-yellow-700/90 text-white px-10 py-5 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-yellow-500/90 dark:hover:bg-yellow-600/90">
                    Заказать доставку
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
