<script>
export default {
    name: "MobileMenu",
    props: {
        isOpen: {
            type: Boolean,
            required: true,
        },
    },
    emits: ["close"],
    methods: {
        closeMenu() {
            this.$emit("close");
        },
    },
};
</script>

<template>
    <!-- Мобильное меню - полноэкранное -->
    <transition name="mobile-menu">
        <div
            v-if="isOpen"
            class="lg:hidden fixed inset-0 bg-[#003859] z-[100] flex flex-col"
        >
            <!-- Верхняя часть - Header -->
            <div class="flex justify-between items-center px-6 py-6">
                <!-- Логотип -->
                <router-link
                    to="/"
                    @click="closeMenu"
                    class="flex items-center space-x-3"
                >
                    <div
                        class="w-12 h-12 bg-[#C20A6C] rounded-full flex items-center justify-center shadow-lg flex-shrink-0"
                    >
                        <svg
                            class="w-6 h-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M6 4C6 4 4 6 4 10C4 14 6 16 6 20M18 4C18 4 20 6 20 10C20 14 18 16 18 20M6 4L18 4M6 20L18 20"
                                stroke="white"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M8 8C8 8 7 9 7 11C7 13 8 14 8 16M16 8C16 8 17 9 17 11C17 13 16 14 16 16"
                                stroke="white"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-jost-regular text-white leading-tight"
                        >
                            ОСНОВАНО 2020
                        </span>
                        <span
                            class="text-lg font-jost-bold text-white leading-tight"
                        >
                            ЗАТОЧКА<span class="text-[#C20A6C]">.</span>ТСК
                        </span>
                        <span
                            class="text-[10px] font-jost-regular text-white leading-tight"
                        >
                            ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
                        </span>
                    </div>
                </router-link>

                <!-- Кнопка закрытия -->
                <button
                    @click="closeMenu"
                    class="w-10 h-10 flex items-center justify-center text-white hover:text-[#C20A6C] transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 rounded-lg"
                    aria-label="Закрыть меню"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Средняя часть - Навигационные ссылки -->
            <nav
                class="flex-1 flex flex-col justify-center items-center space-y-6 px-6"
            >
                <router-link
                    to="/"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    ГЛАВНАЯ
                </router-link>
                <router-link
                    to="/sharpening"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    ЗАТОЧКА
                </router-link>
                <router-link
                    to="/repair"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    РЕМОНТ
                </router-link>
                <router-link
                    to="/delivery"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    ДОСТАВКА
                </router-link>
                <router-link
                    to="/price"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    ПРАЙС
                </router-link>
                <router-link
                    to="/schedule"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    ГРАФИК РАБОТЫ
                </router-link>
                <router-link
                    to="/contacts"
                    @click="closeMenu"
                    class="text-white text-2xl font-jost-bold transition-all duration-300 hover:scale-105"
                >
                    КОНТАКТЫ
                </router-link>
            </nav>

            <!-- Нижняя часть - Социальные иконки и инструменты -->
            <div class="relative pb-8">
                <!-- Белый изогнутый фон -->
                <div
                    class="absolute bottom-0 left-0 right-0 h-32 bg-white rounded-t-[40px]"
                ></div>

                <div class="relative px-6 pt-8">
                    <!-- Социальные иконки -->
                    <div
                        class="flex justify-center items-center space-x-4 mb-6"
                    >
                        <!-- WhatsApp -->
                        <a
                            href="https://wa.me/79999999999"
                            target="_blank"
                            rel="noopener"
                            class="w-12 h-12 bg-[#003859] rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300"
                        >
                            <svg
                                class="w-6 h-6 text-white"
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
                            class="w-12 h-12 bg-[#003859] rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300"
                        >
                            <svg
                                class="w-6 h-6 text-white"
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
                            class="w-12 h-12 bg-[#003859] rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300"
                        >
                            <svg
                                class="w-6 h-6 text-white"
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
                            class="w-12 h-12 bg-[#003859] rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300"
                        >
                            <svg
                                class="w-6 h-6 text-white"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.698-1.72-2.53-.012-2.53-.012s-.39.39-.895.39c-.66 0-1.22-.39-1.22-1.5v-4.5c0-1.11.56-1.5 1.22-1.5.505 0 .895.39.895.39s.832-1.708 2.53-.012c1.186 1.202 1.39 1.727 2.05 1.727h1.744l-.39 3.634z"
                                />
                            </svg>
                        </a>
                    </div>

                    <!-- Иконки инструментов -->
                    <div class="flex justify-center items-center space-x-4">
                        <!-- Ножницы -->
                        <div class="w-8 h-8 text-[#C20A6C]">
                            <svg
                                fill="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M9.5 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm5.5 4l-1.5 1.5L13 5l1.5-1.5L14.5 2 13 3.5 11.5 2 10 3.5 11.5 5 10 6.5 11.5 8l1.5-1.5L14.5 8 16 6.5 14.5 5 16 3.5 14.5 2zm-5 5c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-2 4c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm8 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4 4c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"
                                />
                            </svg>
                        </div>

                        <!-- Лезвие -->
                        <div class="w-8 h-8 text-[#C20A6C]">
                            <svg
                                fill="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M20.66 7.83l-2.12-2.12a.996.996 0 0 0-1.41 0L16.34 6.5l-1.41-1.41a.996.996 0 0 0-1.41 0L12.5 6.5l-1.41-1.41a.996.996 0 0 0-1.41 0L8.66 6.5 7.24 5.09a.996.996 0 0 0-1.41 0L3.83 7.5c-.78.78-.78 2.05 0 2.83l.71.71L2 13.24l.71.71L6.34 10l1.41 1.41L6.34 13l1.41 1.41L10.17 11l1.41 1.41L10.17 14l1.41 1.41L14 12.24l1.41 1.41L14 15l1.41 1.41L18.17 13l.71.71.71-.71-2.12-2.12.71-.71c.78-.78.78-2.05 0-2.83z"
                                />
                            </svg>
                        </div>

                        <!-- Линейка -->
                        <div class="w-8 h-8 text-[#C20A6C]">
                            <svg
                                fill="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"
                                />
                            </svg>
                        </div>

                        <!-- Еще лезвие -->
                        <div class="w-8 h-8 text-[#C20A6C]">
                            <svg
                                fill="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M20.66 7.83l-2.12-2.12a.996.996 0 0 0-1.41 0L16.34 6.5l-1.41-1.41a.996.996 0 0 0-1.41 0L12.5 6.5l-1.41-1.41a.996.996 0 0 0-1.41 0L8.66 6.5 7.24 5.09a.996.996 0 0 0-1.41 0L3.83 7.5c-.78.78-.78 2.05 0 2.83l.71.71L2 13.24l.71.71L6.34 10l1.41 1.41L6.34 13l1.41 1.41L10.17 11l1.41 1.41L10.17 14l1.41 1.41L14 12.24l1.41 1.41L14 15l1.41 1.41L18.17 13l.71.71.71-.71-2.12-2.12.71-.71c.78-.78.78-2.05 0-2.83z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
/* Анимация мобильного меню */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition: opacity 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
    opacity: 0;
}
</style>
