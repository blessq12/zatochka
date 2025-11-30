<script>
export default {
    name: "Footer",
    data() {
        return {
            currentYear: new Date().getFullYear(),
        };
    },
};
</script>

<template>
    <footer
        class="bg-white/85 backdrop-blur-2xl border-t border-white/25 dark:bg-gray-900/85 dark:backdrop-blur-2xl dark:border-gray-800/25 mt-auto"
    >
        <div class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-12 sm:py-16 lg:py-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 sm:gap-16 lg:gap-20">
                <!-- О компании -->
                <div>
                    <h3
                        class="text-2xl font-jost-bold text-dark-gray-500 dark:text-gray-100 mb-6"
                    >
                        О компании
                    </h3>
                    <p
                        class="text-lg font-jost-regular text-gray-500 dark:text-gray-200 mb-4"
                    >
                        Профессиональная заточка и ремонт инструментов
                    </p>
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-10 h-10 bg-blue-500 rounded-2xl flex items-center justify-center shadow-lg"
                        >
                            <span class="text-white font-jost-bold text-lg">З</span>
                        </div>
                        <span
                            class="text-xl font-jost-bold text-dark-blue-500 dark:text-blue-300"
                        >
                            Заточка
                        </span>
                    </div>
                </div>

                <!-- Навигация -->
                <div>
                    <h3
                        class="text-2xl font-jost-bold text-dark-gray-500 dark:text-gray-100 mb-6"
                    >
                        Навигация
                    </h3>
                    <nav class="flex flex-col space-y-3">
                        <router-link
                            to="/"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Главная
                        </router-link>
                        <router-link
                            to="/sharpening"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Заточка
                        </router-link>
                        <router-link
                            to="/repair"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Ремонт
                        </router-link>
                        <router-link
                            to="/delivery"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Доставка
                        </router-link>
                        <router-link
                            to="/contacts"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Контакты
                        </router-link>
                    </nav>
                </div>

                <!-- Правовая информация -->
                <div>
                    <h3
                        class="text-2xl font-jost-bold text-dark-gray-500 dark:text-gray-100 mb-6"
                    >
                        Информация
                    </h3>
                    <nav class="flex flex-col space-y-3">
                        <router-link
                            to="/privacy-policy"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Политика конфиденциальности
                        </router-link>
                        <router-link
                            to="/terms-of-service"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Условия использования
                        </router-link>
                        <router-link
                            to="/help"
                            class="text-lg font-jost-medium text-gray-500 hover:text-blue-500 transition-colors duration-300 dark:text-gray-300 dark:hover:text-blue-400"
                        >
                            Помощь
                        </router-link>
                    </nav>
                </div>
            </div>

            <!-- Копирайт -->
            <div
                class="mt-12 sm:mt-16 lg:mt-20 pt-8 sm:pt-10 lg:pt-12 border-t border-white/20 dark:border-gray-700/20"
            >
                <p
                    class="text-center text-lg font-jost-regular text-gray-500 dark:text-gray-400"
                >
                    © {{ currentYear }} Заточка. Все права защищены.
                </p>
            </div>
        </div>
    </footer>
</template>

<style scoped></style>

