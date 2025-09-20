<x-layouts.app title="Помощь">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-16">
        <div
            class="bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-12 sm:p-16 lg:p-20 border border-white/25 text-center dark:bg-gray-800/90 dark:border-gray-600/30">
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-jost-bold text-dark-gray-500 mb-8 dark:text-gray-100">
                <span class="bg-gradient-to-r from-blue-500 to-dark-blue-500 bg-clip-text text-transparent">Помощь</span>
            </h1>
            <p
                class="text-xl sm:text-2xl lg:text-3xl font-jost-regular text-gray-500 mb-12 dark:text-gray-200 max-w-4xl mx-auto">
                Найдите ответы на часто задаваемые вопросы или свяжитесь с нами для получения персональной помощи
            </p>
        </div>
    </div>

    <!-- Interactive Help Content -->
    <div class="max-w-7xl mx-auto">
        <help-page :contacts="{{ json_encode($contacts ?? []) }}"></help-page>
    </div>
</x-layouts.app>
