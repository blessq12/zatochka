<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import ThemeToggler from "../Support/ThemeToggler.vue";
import MobileMenu from "./MobileMenu.vue";

export default {
    name: "Navigation",
    components: {
        ThemeToggler,
        MobileMenu,
    },
    data() {
        return {
            isMobileMenuOpen: false,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    mounted() {
        this.authStore.checkAuth();
    },
    methods: {
        toggleMobileMenu() {
            this.isMobileMenuOpen = !this.isMobileMenuOpen;
        },
        closeMobileMenu() {
            this.isMobileMenuOpen = false;
        },
    },
};
</script>

<template>
    <nav class="bg-[#C20A6C] sticky top-0 z-50">
        <div class="container mx-auto">
            <div
                class="flex justify-between items-center py-4 h-20 px-6 sm:px-8 lg:px-12"
            >
                <!-- Логотип -->
                <router-link
                    to="/"
                    class="flex items-center space-x-2 sm:space-x-3 group focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C] rounded-xl -m-2 flex-shrink-0"
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
                            fill="#003859"
                        />
                        <path
                            d="M32.9363 12.1868C32.9363 12.1868 32.9421 12.183 32.944 12.181C32.9421 12.1791 32.9382 12.1772 32.9363 12.1753C34.5178 11.0221 35.5988 9.27314 35.6558 6.92665C35.5493 2.4487 31.7241 0.147881 27.7847 0.0108596V0.00515036C27.7847 0.00515036 23.3239 -0.24225 20.5264 2.6923L23.1697 4.37272C25.0842 2.77032 27.9579 3.01963 27.9579 3.01963C29.0997 3.09004 32.0952 3.96926 32.2246 6.92474C32.0762 10.3217 27.3775 10.396 26.896 10.396V13.9661C27.3755 13.9661 32.0762 14.0404 32.2246 17.4374C32.0971 20.3909 29.1016 21.2721 27.9579 21.3425C27.9579 21.3425 25.0842 21.5918 23.1697 19.9894L20.5264 21.6698C23.3258 24.6043 27.7847 24.3569 27.7847 24.3569V24.3512C31.7241 24.2142 35.5512 21.9153 35.6577 17.4374C35.6007 15.0909 34.5197 13.3419 32.9382 12.1887L32.9363 12.1868Z"
                            fill="#003859"
                        />
                        <path
                            d="M13.352 17.2355L10.4098 17.245L18.8043 12.1847H18.7986L18.8043 12.179L10.4098 7.1187L13.352 7.12821L22.0396 10.6679C21.2955 5.2632 16.7052 1.08594 11.0969 1.08594C4.96704 1.08594 0 6.05297 0 12.1809C0 18.3088 4.96704 23.2777 11.0969 23.2777C16.7052 23.2777 21.2955 19.1005 22.0396 13.6957L13.352 17.2355Z"
                            fill="#003859"
                        />
                    </svg>

                    <!-- Текст логотипа -->
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] font-jost-regular text-white leading-tight"
                        >
                            ОСНОВАНО 2020
                        </span>
                        <span
                            class="text-lg sm:text-xl font-jost-bold text-white leading-tight"
                        >
                            ЗАТОЧКА<span class="text-[#003859]">.</span>ТСК
                        </span>
                        <span
                            class="text-[10px] sm:text-xs font-jost-regular text-white leading-tight"
                        >
                            ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
                        </span>
                    </div>
                </router-link>

                <!-- Центральная часть - навигационные ссылки (десктоп) -->
                <nav
                    class="hidden lg:flex items-center space-x-1 xl:space-x-2 flex-1 justify-center mx-8"
                >
                    <router-link
                        to="/"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        ГЛАВНАЯ
                    </router-link>
                    <router-link
                        to="/sharpening"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        ЗАТОЧКА
                    </router-link>
                    <router-link
                        to="/repair"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        РЕМОНТ
                    </router-link>
                    <router-link
                        to="/delivery"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        ДОСТАВКА
                    </router-link>
                    <router-link
                        to="/prices"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        ПРАЙС
                    </router-link>
                    <router-link
                        to="/work-schedule"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        ГРАФИК
                    </router-link>
                    <router-link
                        to="/contacts"
                        class="text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 rounded-xl hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                        active-class="bg-white/30 font-jost-bold"
                    >
                        КОНТАКТЫ
                    </router-link>
                </nav>

                <div
                    class="hidden lg:flex items-center space-x-3 flex-shrink-0"
                >
                    <ThemeToggler />
                    <router-link
                        v-if="authStore.isAuthenticated"
                        to="/client/dashboard"
                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl font-jost-bold text-sm transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                    >
                        В ПАНЕЛЬ
                    </router-link>
                    <router-link
                        v-else
                        to="/client/dashboard"
                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-xl font-jost-bold text-sm transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap"
                    >
                        ВОЙТИ
                    </router-link>
                </div>

                <!-- Правая часть - мобильный -->
                <div class="flex lg:hidden items-center space-x-3">
                    <ThemeToggler />
                    <button
                        @click="toggleMobileMenu"
                        class="w-10 h-10 flex flex-col justify-center items-center space-y-1.5 focus:outline-none focus:ring-2 focus:ring-white/50 rounded-lg"
                        aria-label="Меню"
                    >
                        <span
                            class="block w-6 h-0.5 bg-black transition-all duration-300"
                            :class="{
                                'rotate-45 translate-y-2': isMobileMenuOpen,
                            }"
                        ></span>
                        <span
                            class="block w-6 h-0.5 bg-black transition-all duration-300"
                            :class="{ 'opacity-0': isMobileMenuOpen }"
                        ></span>
                        <span
                            class="block w-6 h-0.5 bg-black transition-all duration-300"
                            :class="{
                                '-rotate-45 -translate-y-2': isMobileMenuOpen,
                            }"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Мобильное меню -->
            <MobileMenu :is-open="isMobileMenuOpen" @close="closeMobileMenu" />
        </div>
    </nav>
</template>

<style scoped></style>
