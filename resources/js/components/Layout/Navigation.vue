<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import DashboardButton from "../Support/DashboardButton.vue";
import ThemeToggler from "../Support/ThemeToggler.vue";
import MobileMenu from "./MobileMenu.vue";

export default {
    name: "Navigation",
    components: {
        DashboardButton,
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
        <div class="container mx-auto px-6 sm:px-8 lg:px-12">
            <div class="flex justify-between items-center py-4">
                <!-- Логотип -->
                <router-link
                    to="/"
                    class="flex items-center space-x-2 sm:space-x-3 group focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C] rounded-xl p-2 -m-2 flex-shrink-0"
                >
                    <!-- Иконка логотипа -->
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 bg-[#003859] rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105 flex-shrink-0"
                    >
                        <!-- SVG иконка лезвия/заточки (форма похожая на "3" или изогнутое лезвие) -->
                        <svg
                            class="w-6 h-6 sm:w-7 sm:h-7"
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

                    <!-- Текст логотипа -->
                    <div class="flex flex-col">
                        <span class="text-[10px] font-jost-regular text-white leading-tight">
                            ОСНОВАНО 2020
                        </span>
                        <span class="text-lg sm:text-xl font-jost-bold text-white leading-tight">
                            ЗАТОЧКА<span class="text-[#003859]">.</span>ТСК
                        </span>
                        <span
                            class="text-[10px] sm:text-xs font-jost-regular text-white leading-tight"
                        >
                            ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
                        </span>
                    </div>
                </router-link>

                <!-- Правая часть - десктоп -->
                <div class="hidden lg:flex items-center space-x-3 flex-shrink-0">
                    <ThemeToggler />
                    <router-link
                        v-if="authStore.isAuthenticated"
                        to="/client/dashboard"
                        class="bg-[#003859] hover:bg-[#002c4e] text-white px-5 py-2.5 rounded-xl font-jost-bold text-sm transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C] whitespace-nowrap"
                    >
                        В панель
                    </router-link>
                    <router-link
                        v-else
                        to="/client/dashboard"
                        class="bg-[#003859] hover:bg-[#002c4e] text-white px-5 py-2.5 rounded-xl font-jost-bold text-sm transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C] whitespace-nowrap"
                    >
                        Войти
                    </router-link>
                </div>

                <!-- Правая часть - мобильный -->
                <div class="flex lg:hidden items-center space-x-3">
                    <ThemeToggler />
                    <router-link
                        v-if="authStore.isAuthenticated"
                        to="/client/dashboard"
                        class="bg-[#003859] hover:bg-[#002c4e] text-white px-4 py-2 rounded-xl font-jost-bold text-sm transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C]"
                    >
                        В панель
                    </router-link>
                    <router-link
                        v-else
                        to="/client/dashboard"
                        class="bg-[#003859] hover:bg-[#002c4e] text-white px-4 py-2 rounded-xl font-jost-bold text-sm transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C]"
                    >
                        Войти
                    </router-link>
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

