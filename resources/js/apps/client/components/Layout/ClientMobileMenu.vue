<script>
import { isClientNavActive } from "../../navigation.js";

export default {
    name: "ClientMobileMenu",
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        items: {
            type: Array,
            default: () => [],
        },
    },
    emits: ["close", "logout"],
    methods: {
        isActive(item) {
            return isClientNavActive(item, this.$route);
        },
        onNavigate() {
            this.$emit("close");
        },
        onLogout() {
            this.$emit("close");
            this.$emit("logout");
        },
        linkClass(item) {
            const base =
                "px-4 py-2 text-xl font-jost-bold text-white transition-all duration-300 sm:text-2xl";
            return this.isActive(item) ? `${base} bg-white/20` : base;
        },
    },
};
</script>

<template>
    <Teleport to="body">
        <transition name="client-mobile-menu">
            <div
                v-if="open"
                class="fixed inset-x-0 top-[calc(4rem+env(safe-area-inset-top))] bottom-0 z-[100] flex flex-col bg-[#003859] sm:top-[calc(5rem+env(safe-area-inset-top))] lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="Меню кабинета"
            >
                <nav
                    class="flex flex-1 flex-col items-center justify-center gap-4 overflow-y-auto px-6 py-6 sm:gap-5"
                >
                    <a
                        href="/"
                        class="px-4 py-2 text-xl font-jost-bold text-white transition-all duration-300 sm:text-2xl"
                        @click="onNavigate"
                    >
                        НА САЙТ
                    </a>
                    <router-link
                        v-for="item in items"
                        :key="item.name"
                        :to="item.to"
                        :class="linkClass(item)"
                        @click="onNavigate"
                    >
                        {{ item.label.toUpperCase() }}
                    </router-link>
                </nav>

                <div
                    class="border-t border-white/15 px-6 py-4 pb-[max(1rem,env(safe-area-inset-bottom))]"
                >
                    <button
                        type="button"
                        class="w-full bg-[#C20A6C] py-3 font-jost-bold text-white transition hover:bg-[#a0085a]"
                        @click="onLogout"
                    >
                        ВЫХОД
                    </button>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>
.client-mobile-menu-enter-active,
.client-mobile-menu-leave-active {
    transition: opacity 0.25s ease;
}
.client-mobile-menu-enter-from,
.client-mobile-menu-leave-to {
    opacity: 0;
}
</style>
