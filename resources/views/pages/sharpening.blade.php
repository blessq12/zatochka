<x-layouts.app title="Заточка инструментов">
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-8 dark:text-gray-100">Заточка инструментов</h1>
            <p class="text-xl sm:text-2xl text-gray-700 mb-12 dark:text-gray-300">
                Профессиональная заточка различных видов режущего инструмента с использованием современного
                оборудования.
            </p>

            <div
                class="bg-blue-50/80 backdrop-blur-lg rounded-2xl p-10 mb-12 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20">
                <h2 class="text-2xl font-semibold text-blue-700 mb-6 dark:text-blue-400">Наши услуги по заточке:</h2>
                <ul class="space-y-4 text-lg text-blue-600 dark:text-blue-300">
                    <li>• Ножи кухонные и профессиональные</li>
                    <li>• Ножницы парикмахерские и бытовые</li>
                    <li>• Стамески и долота</li>
                    <li>• Топоры и колуны</li>
                    <li>• Садовый инструмент</li>
                </ul>
            </div>

            <div class="text-center">
                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">Страница в разработке...</p>
                <a href="{{ route('contacts') }}"
                    class="bg-blue-600/90 backdrop-blur-md hover:bg-blue-700/90 text-white px-10 py-5 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90">
                    Связаться с нами
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
