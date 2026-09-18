<script>
import { isClientNavActive } from "../../navigation.js";

export default {
    name: "ClientNavTabs",
    props: {
        items: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        isActive(item) {
            return isClientNavActive(item, this.$route);
        },
    },
};
</script>

<template>
    <nav
        class="mb-8 hidden flex-wrap gap-2 bg-white/60 p-2 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60 sm:gap-4 sm:mb-12 lg:flex"
        aria-label="Разделы кабинета"
    >
        <router-link
            v-for="item in items"
            :key="item.name"
            :to="item.to"
            class="flex-1 px-4 py-4 text-center font-jost-bold text-base transition-all duration-300 sm:text-lg"
            :class="
                isActive(item)
                    ? 'bg-[#C3006B] text-white shadow-lg'
                    : 'text-dark-gray-500 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80'
            "
        >
            {{ item.label }}
        </router-link>
    </nav>
</template>
