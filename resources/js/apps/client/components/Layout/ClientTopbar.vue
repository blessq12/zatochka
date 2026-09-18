<script>
import { isClientNavActive } from "../../navigation.js";

const BACK_TO = {
    "client.orders.show": { name: "client.orders" },
    "client.equipment.show": { name: "client.equipment" },
};

export default {
    name: "ClientTopbar",
    props: {
        title: {
            type: String,
            default: "Кабинет",
        },
        items: {
            type: Array,
            default: () => [],
        },
    },
    computed: {
        backTo() {
            const name = String(this.$route.name || "");
            return BACK_TO[name] || null;
        },
    },
    methods: {
        isActive(item) {
            return isClientNavActive(item, this.$route);
        },
        goBack() {
            if (this.backTo) {
                this.$router.push(this.backTo);
            }
        },
        linkClass(item) {
            const base =
                "text-white text-xs xl:text-sm font-jost-medium px-3 xl:px-4 py-2 hover:bg-white/20 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50 whitespace-nowrap uppercase";
            return this.isActive(item)
                ? `${base} bg-white/30 font-jost-bold`
                : base;
        },
    },
};
</script>

<template>
    <header
        class="z-[110] shrink-0 bg-[#C20A6C] pt-[env(safe-area-inset-top)]"
    >
        <div class="flex h-12 items-center gap-2 px-3 lg:h-14 lg:px-4">
            <div class="flex w-10 shrink-0 items-center justify-start">
                <button
                    v-if="backTo"
                    type="button"
                    class="flex h-10 w-10 items-center justify-center text-white focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Назад"
                    @click="goBack"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>
            </div>

            <h1
                class="min-w-0 flex-1 truncate text-center text-base font-jost-bold uppercase text-white lg:flex-none lg:text-left"
            >
                {{ title }}
            </h1>

            <nav
                v-if="items.length"
                class="hidden flex-1 items-center justify-end gap-1 lg:flex"
                aria-label="Разделы кабинета"
            >
                <router-link
                    v-for="item in items"
                    :key="item.name"
                    :to="item.to"
                    :class="linkClass(item)"
                >
                    {{ item.label }}
                </router-link>
            </nav>
            <div class="w-10 shrink-0 lg:hidden" />
        </div>
    </header>
</template>
