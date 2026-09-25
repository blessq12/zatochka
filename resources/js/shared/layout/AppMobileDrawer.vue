<script>
import AppNavIcon from "./AppNavIcon.vue";
import BrandMark from "./BrandMark.vue";
import { isNavItemActive } from "./navActive.js";

export default {
    name: "AppMobileDrawer",
    components: { AppNavIcon, BrandMark },
    props: {
        tagline: { type: String, default: "" },
        items: { type: Array, default: () => [] },
        open: { type: Boolean, default: false },
    },
    emits: ["close"],
    methods: {
        isActive(item) {
            return isNavItemActive(item, this.$route);
        },
        iconName(item) {
            return item.icon || "dashboard";
        },
        onNavigate() {
            this.$emit("close");
        },
    },
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[998] bg-black/50 lg:hidden"
            aria-hidden="true"
            @click="$emit('close')"
        />

        <aside
            class="fixed inset-y-0 left-0 z-[999] flex w-[min(20rem,88vw)] max-w-full flex-col bg-pink-500 text-white shadow-xl transition-transform duration-300 ease-out lg:hidden"
            :class="
                open
                    ? 'translate-x-0'
                    : 'pointer-events-none -translate-x-full'
            "
            :aria-hidden="open ? 'false' : 'true'"
            role="dialog"
            aria-modal="true"
            aria-label="Меню"
        >
            <div
                class="flex items-start justify-between gap-2 border-b border-white/15 px-4 py-4 pt-[max(1rem,env(safe-area-inset-top))]"
            >
                <BrandMark :tagline="tagline" />
                <button
                    type="button"
                    class="rounded p-2 text-white/90 hover:bg-white/10"
                    aria-label="Закрыть меню"
                    @click="$emit('close')"
                >
                    <svg
                        class="h-5 w-5"
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
                class="flex-1 space-y-1 overflow-y-auto px-2 py-3 pb-[max(1rem,env(safe-area-inset-bottom))]"
            >
                <router-link
                    v-for="item in items"
                    :key="item.label"
                    :to="item.to"
                    class="flex items-center gap-3 px-3 py-3 text-base font-jost-medium transition-colors"
                    :class="
                        isActive(item)
                            ? 'bg-white/20 text-white'
                            : 'text-white hover:bg-white/15'
                    "
                    @click="onNavigate"
                >
                    <AppNavIcon
                        :name="iconName(item)"
                        :active="isActive(item)"
                        class="!h-5 !w-5"
                    />
                    <span>{{ item.label }}</span>
                </router-link>
            </nav>
        </aside>
    </Teleport>
</template>
