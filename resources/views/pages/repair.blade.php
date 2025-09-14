<x-layouts.app title="Ремонт инструментов">
    <div class="max-w-4xl mx-auto">
        <div
            class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/20 dark:bg-gray-900/80 dark:border-gray-800/20">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-8 dark:text-gray-100">Ремонт инструментов</h1>
            <p class="text-xl sm:text-2xl text-gray-700 mb-12 dark:text-gray-300">
                Восстановление и ремонт поврежденного инструмента. Мы вернем вашим инструментам работоспособность.
            </p>

            <div
                class="bg-green-50/80 backdrop-blur-lg rounded-2xl p-10 mb-12 border border-green-200/30 dark:bg-green-900/30 dark:border-green-800/20">
                <h2 class="text-2xl font-semibold text-green-700 mb-6 dark:text-green-400">Виды ремонта:</h2>
                <ul class="space-y-4 text-lg text-green-600 dark:text-green-300">
                    <li>• Ремонт ручек и черенков</li>
                    <li>• Восстановление режущих кромок</li>
                    <li>• Замена крепежных элементов</li>
                    <li>• Реставрация деревянных частей</li>
                    <li>• Восстановление защитных покрытий</li>
                </ul>
            </div>

            <div class="text-center">
                <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">Страница в разработке...</p>
                <a href="{{ route('contacts') }}"
                    class="bg-green-600/90 backdrop-blur-md hover:bg-green-700/90 text-white px-10 py-5 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-green-500/90 dark:hover:bg-green-600/90">
                    Заказать ремонт
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
