<script>
import AppNavIcon from "@shared/layout/AppNavIcon.vue";
import { isClientNavActive } from "../../navigation.js";

export default {
    name: "ClientBottomNav",
    components: { AppNavIcon },
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
        iconName(item) {
            return item.icon || "dashboard";
        },
    },
};
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-[900] border-t border-dark-blue-500/20 bg-white/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-md lg:hidden dark:border-white/10 dark:bg-dark-blue-500/95"
        aria-label="Основная навигация"
    >
        <ul
            class="mx-auto grid h-12 w-full max-w-lg"
            :style="{
                gridTemplateColumns: `repeat(${Math.max(items.length, 1)}, minmax(0, 1fr))`,
            }"
        >
            <li v-for="item in items" :key="item.name" class="min-w-0">
                <router-link
                    :to="item.to"
                    class="relative flex h-full flex-col items-center justify-center px-1 transition-colors"
                    :class="
                        isActive(item)
                            ? 'text-[#C3006B]'
                            : 'text-dark-gray-500 dark:text-gray-300'
                    "
                    :aria-label="item.label"
                    :aria-current="isActive(item) ? 'page' : undefined"
                >
                    <span
                        class="absolute inset-x-4 top-0 h-0.5 transition-colors"
                        :class="
                            isActive(item) ? 'bg-[#C3006B]' : 'bg-transparent'
                        "
                        aria-hidden="true"
                    />
                    <AppNavIcon
                        :name="iconName(item)"
                        :active="isActive(item)"
                    />
                </router-link>
            </li>
        </ul>
    </nav>
</template>
