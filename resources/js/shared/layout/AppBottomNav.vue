<script>
import AppNavIcon from "./AppNavIcon.vue";
import { isNavItemActive } from "./navActive.js";

export default {
    name: "AppBottomNav",
    components: { AppNavIcon },
    props: {
        /** @type {{ label: string, icon?: string, to: string|object, match?: string, name?: string }[]} */
        items: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        isActive(item) {
            return isNavItemActive(item, this.$route);
        },
        iconName(item) {
            return item.icon || "dashboard";
        },
    },
};
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-[900] border-t border-slate-300 bg-white pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_16px_rgba(15,23,42,0.1)] lg:hidden"
        aria-label="Основная навигация"
    >
        <ul
            class="mx-auto grid h-14 w-full max-w-lg"
            :style="{ gridTemplateColumns: `repeat(${Math.max(items.length, 1)}, minmax(0, 1fr))` }"
        >
            <li v-for="item in items" :key="item.label" class="min-w-0">
                <router-link
                    :to="item.to"
                    class="relative flex h-full flex-col items-center justify-center gap-1 transition-colors"
                    :class="
                        isActive(item)
                            ? 'text-pink-700'
                            : 'text-slate-500 active:bg-slate-50 active:text-slate-700'
                    "
                    :aria-label="item.label"
                    :aria-current="isActive(item) ? 'page' : undefined"
                >
                    <span
                        class="absolute inset-x-3 top-0 h-0.5 rounded-full transition-colors"
                        :class="isActive(item) ? 'bg-pink-500' : 'bg-transparent'"
                        aria-hidden="true"
                    />
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full transition-colors"
                        :class="isActive(item) ? 'bg-pink-100' : 'bg-transparent'"
                    >
                        <AppNavIcon
                            :name="iconName(item)"
                            :active="isActive(item)"
                        />
                    </span>
                </router-link>
            </li>
        </ul>
    </nav>
</template>
