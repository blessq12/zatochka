<script>
export default {
    name: "Footer",
    data() {
        return {
            isDark: false,
        };
    },
    computed: {
        logoColor() {
            return this.isDark ? "#C20A6C" : "#003859";
        },
    },
    mounted() {
        this.updateThemeState();
        // Отслеживаем изменения темы
        const observer = new MutationObserver(() => {
            this.updateThemeState();
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
    },
    methods: {
        updateThemeState() {
            this.isDark = document.documentElement.classList.contains("dark");
        },
    },
};
</script>

<template>
    <footer
        class="bg-white/85 backdrop-blur-2xl border-t border-white/25 dark:bg-dark-blue-500/85 dark:backdrop-blur-2xl dark:border-gray-800/25 mt-auto"
    >
        <div
            class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-12 sm:py-16 lg:py-20"
        >
            <!-- Логотип по центру -->
            <div class="flex flex-col items-center space-y-6">
                <router-link
                    to="/"
                    class="flex items-center space-x-2 sm:space-x-3 group focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 focus:ring-offset-white rounded-xl p-2 -m-2"
                >
                    <!-- Иконка логотипа -->
                    <svg
                        width="36"
                        height="25"
                        viewBox="0 0 36 25"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-12 h-8 sm:w-14 sm:h-10 flex-shrink-0 group-hover:scale-105 transition-all duration-300"
                    >
                        <path
                            d="M25.3397 12.1789C25.3397 11.6708 24.9268 11.2578 24.4186 11.2578C23.9105 11.2578 23.4976 11.6708 23.4976 12.1789C23.4976 12.687 23.9105 13.1 24.4186 13.1C24.9268 13.1 25.3397 12.6889 25.3397 12.1789Z"
                            :fill="logoColor"
                        />
                        <path
                            d="M32.9363 12.1868C32.9363 12.1868 32.9421 12.183 32.944 12.181C32.9421 12.1791 32.9382 12.1772 32.9363 12.1753C34.5178 11.0221 35.5988 9.27314 35.6558 6.92665C35.5493 2.4487 31.7241 0.147881 27.7847 0.0108596V0.00515036C27.7847 0.00515036 23.3239 -0.24225 20.5264 2.6923L23.1697 4.37272C25.0842 2.77032 27.9579 3.01963 27.9579 3.01963C29.0997 3.09004 32.0952 3.96926 32.2246 6.92474C32.0762 10.3217 27.3775 10.396 26.896 10.396V13.9661C27.3755 13.9661 32.0762 14.0404 32.2246 17.4374C32.0971 20.3909 29.1016 21.2721 27.9579 21.3425C27.9579 21.3425 25.0842 21.5918 23.1697 19.9894L20.5264 21.6698C23.3258 24.6043 27.7847 24.3569 27.7847 24.3569V24.3512C31.7241 24.2142 35.5512 21.9153 35.6577 17.4374C35.6007 15.0909 34.5197 13.3419 32.9382 12.1887L32.9363 12.1868Z"
                            :fill="logoColor"
                        />
                        <path
                            d="M13.352 17.2355L10.4098 17.245L18.8043 12.1847H18.7986L18.8043 12.179L10.4098 7.1187L13.352 7.12821L22.0396 10.6679C21.2955 5.2632 16.7052 1.08594 11.0969 1.08594C4.96704 1.08594 0 6.05297 0 12.1809C0 18.3088 4.96704 23.2777 11.0969 23.2777C16.7052 23.2777 21.2955 19.1005 22.0396 13.6957L13.352 17.2355Z"
                            :fill="logoColor"
                        />
                    </svg>
                    <!-- Текст логотипа -->
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-jost-regular text-dark-gray-500 dark:text-gray-300 leading-tight"
                        >
                            ОСНОВАНО 2020
                        </span>
                        <span
                            class="text-lg sm:text-xl font-jost-bold text-dark-gray-500 dark:text-gray-100 leading-tight"
                        >
                            ЗАТОЧКА<span class="text-[#C20A6C]">.</span>ТСК
                        </span>
                        <span
                            class="text-[10px] sm:text-xs font-jost-regular text-dark-gray-500 dark:text-gray-300 leading-tight"
                        >
                            ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
                        </span>
                    </div>
                </router-link>
                <!-- Ссылки на политику и правила -->
                <div
                    class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-6 mt-6"
                >
                    <router-link
                        to="/privacy-policy"
                        class="text-sm sm:text-base font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-300"
                    >
                        Политика конфиденциальности
                    </router-link>
                    <span
                        class="hidden sm:inline text-dark-gray-500 dark:text-gray-400"
                    >
                        |
                    </span>
                    <router-link
                        to="/terms-of-service"
                        class="text-sm sm:text-base font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-300"
                    >
                        Правила пользования
                    </router-link>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped></style>
