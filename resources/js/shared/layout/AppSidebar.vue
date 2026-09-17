<script>
import BrandMark from "./BrandMark.vue";
import { isNavItemActive } from "./navActive.js";

export default {
    name: "AppSidebar",
    components: { BrandMark },
    props: {
        tagline: {
            type: String,
            default: "",
        },
        /** @type {{ label: string, to: string|object, match?: string, name?: string }[]} */
        items: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        isActive(item) {
            return isNavItemActive(item, this.$route);
        },
    },
};
</script>

<template>
    <aside
        class="app-sidebar sticky top-0 flex h-dvh w-60 shrink-0 flex-col bg-pink-500 text-white"
    >
        <div class="border-b border-white/15 px-4 py-4">
            <BrandMark :tagline="tagline" />
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-3">
            <router-link
                v-for="item in items"
                :key="item.label"
                :to="item.to"
                class="block px-3 py-2.5 text-sm font-jost-medium transition-colors"
                :class="
                    isActive(item)
                        ? 'bg-white/20 text-white'
                        : 'text-white/85 hover:bg-white/10'
                "
            >
                {{ item.label }}
            </router-link>
        </nav>
    </aside>
</template>
