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
        class="bg-white/85 backdrop-blur-2xl border-t border-white/25 dark:bg-gray-800/85 dark:backdrop-blur-2xl dark:border-gray-700/25 mt-auto"
    >
        <div
            class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-8 sm:py-10 lg:py-12"
        >
            <!-- Телефон -->
            <div class="flex justify-center mb-6">
                <a
                    href="tel:+79832335907"
                    class="text-xl sm:text-2xl font-jost-bold text-dark-blue-500 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-300"
                >
                    +7 (983) 233-59-07
                </a>
            </div>
            <!-- Социальные сети -->
            <div class="flex justify-center items-center gap-4 mb-6">
                <!-- WhatsApp -->
                <a
                    href="https://wa.me/79999999999"
                    target="_blank"
                    rel="noopener"
                    class="w-11 h-11 bg-dark-blue-500 hover:bg-blue-500 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-lg hover:shadow-xl"
                    aria-label="WhatsApp"
                >
                    <svg
                        class="w-5 h-5 text-white"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
                        />
                    </svg>
                </a>

                <!-- Instagram -->
                <a
                    href="https://instagram.com/zatochka_tsk"
                    target="_blank"
                    rel="noopener"
                    class="w-11 h-11 bg-dark-blue-500 hover:bg-blue-500 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-lg hover:shadow-xl"
                    aria-label="Instagram"
                >
                    <svg
                        class="w-5 h-5 text-white"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                        />
                    </svg>
                </a>

                <!-- Telegram -->
                <a
                    href="https://t.me/zatochka_tsk"
                    target="_blank"
                    rel="noopener"
                    class="w-11 h-11 bg-dark-blue-500 hover:bg-blue-500 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-lg hover:shadow-xl"
                    aria-label="Telegram"
                >
                    <svg
                        class="w-5 h-5 text-white"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"
                        />
                    </svg>
                </a>

                <!-- VK -->
                <a
                    href="https://vk.com/zatochka_tsk"
                    target="_blank"
                    rel="noopener"
                    class="w-11 h-11 bg-dark-blue-500 hover:bg-blue-500 rounded-full flex items-center justify-center hover:scale-110 transition-all duration-300 shadow-lg hover:shadow-xl"
                    aria-label="VK"
                >
                    <svg
                        class="w-5 h-5 text-white"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.698-1.72-2.53-.012-2.53-.012s-.39.39-.895.39c-.66 0-1.22-.39-1.22-1.5v-4.5c0-1.11.56-1.5 1.22-1.5.505 0 .895.39.895.39s.832-1.708 2.53-.012c1.186 1.202 1.39 1.727 2.05 1.727h1.744l-.39 3.634z"
                        />
                    </svg>
                </a>
            </div>
            <!-- Ссылки на политику и правила -->
            <div
                class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mb-6"
            >
                <router-link
                    to="/privacy-policy"
                    class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-300"
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
                    class="text-sm font-jost-medium text-dark-gray-500 hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-400 transition-colors duration-300"
                >
                    Правила пользования
                </router-link>
            </div>

            <!-- Информация об ИП -->
            <div
                class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 text-xs sm:text-sm font-jost-regular text-gray-500 dark:text-gray-400 mb-4"
            >
                <p>ИП Митькин Максим Игоревич</p>
                <span class="hidden sm:inline">•</span>
                <p>ИНН: 701744164429</p>
                <span class="hidden sm:inline">•</span>
                <p>ОГРН: 323700000001333</p>
            </div>

            <!-- Адреса -->
            <div
                class="flex flex-col items-center justify-center gap-2 sm:gap-3 text-xs sm:text-sm font-jost-regular text-gray-500 dark:text-gray-400"
            >
                <div
                    class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4"
                >
                    <p>
                        <span class="font-jost-medium">Юридический адрес:</span>
                        634033, Томская обл., г. Томск, ул. Короленко, д. 17,
                        кв. 12
                    </p>
                </div>
                <div
                    class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4"
                >
                    <p>
                        <span class="font-jost-medium">Фактический адрес:</span>
                        пер. Карповский, 12 / пр. Ленина, 169
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped></style>
