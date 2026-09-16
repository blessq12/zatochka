<script>
import BrandMark from "./BrandMark.vue";

export default {
    name: "AppSidebar",
    components: { BrandMark },
    props: {
        tagline: {
            type: String,
            default: "",
        },
        /** @type {{ label: string, to: string|object, match?: string }[]} */
        items: {
            type: Array,
            default: () => [],
        },
        mobileOpen: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["close"],
    methods: {
        isActive(item) {
            const route = this.$route;
            if (item.match) {
                const name = String(route.name || "");
                if (item.match === "dashboard") {
                    return name.endsWith(".dashboard") || name === "pos.dashboard";
                }
                return name.includes(`.${item.match}`);
            }
            if (typeof item.to === "string") {
                return route.path === item.to || route.path.startsWith(`${item.to}/`);
            }
            if (item.to?.name) {
                return (
                    route.name === item.to.name
                    || String(route.name || "").startsWith(
                        String(item.to.name).replace(/\.[^.]+$/, ""),
                    )
                );
            }
            return false;
        },
        onNavigate() {
            this.$emit("close");
        },
    },
};
</script>

<template>
    <aside
        class="app-sidebar fixed inset-y-0 left-0 z-[999] flex w-[280px] flex-col bg-pink-500 text-white shadow-lg transition-transform duration-300 lg:static lg:translate-x-0"
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        <div class="flex items-start justify-between gap-2 border-b border-white/15 px-5 py-5">
            <BrandMark :tagline="tagline" />
            <button
                type="button"
                class="lg:hidden rounded p-1 text-white/90 hover:bg-white/10"
                aria-label="Закрыть меню"
                @click="$emit('close')"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
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
                @click="onNavigate"
            >
                {{ item.label }}
            </router-link>
        </nav>
    </aside>
</template>
