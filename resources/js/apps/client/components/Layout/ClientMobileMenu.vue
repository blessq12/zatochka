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
    },
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[1000] flex flex-col bg-[#003859] lg:hidden"
            role="dialog"
            aria-modal="true"
            aria-label="Меню кабинета"
        >
            <div
                class="flex items-center justify-between px-4 pb-3 pt-[max(1rem,env(safe-area-inset-top))]"
            >
                <a
                    href="/"
                    class="font-jost-medium text-white/90 hover:text-white"
                    @click="onNavigate"
                >
                    ← На сайт
                </a>
                <button
                    type="button"
                    class="rounded p-2 text-white/90 hover:bg-white/10"
                    aria-label="Закрыть меню"
                    @click="$emit('close')"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <nav
                class="flex flex-1 flex-col items-center justify-center gap-4 overflow-y-auto px-6 py-6 sm:gap-5"
            >
                <router-link
                    v-for="item in items"
                    :key="item.name"
                    :to="item.to"
                    class="px-4 py-2 text-xl font-jost-bold text-white transition-all duration-300 sm:text-2xl"
                    :class="isActive(item) ? 'bg-white/20' : ''"
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
                    class="w-full bg-[#C3006B] py-3 font-jost-bold text-white hover:bg-[#A8005A]"
                    @click="onLogout"
                >
                    Выход
                </button>
            </div>
        </div>
    </Teleport>
</template>
