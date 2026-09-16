<script>
export default {
    name: "AppTopbar",
    props: {
        userName: {
            type: String,
            default: "",
        },
        userEmail: {
            type: String,
            default: "",
        },
    },
    emits: ["logout", "toggle-mobile"],
    computed: {
        initials() {
            const parts = String(this.userName || "")
                .trim()
                .split(/\s+/)
                .filter(Boolean);
            if (parts.length === 0) return "?";
            if (parts.length === 1) return parts[0].slice(0, 1).toUpperCase();
            return (parts[0][0] + parts[1][0]).toUpperCase();
        },
    },
};
</script>

<template>
    <header class="flex h-14 shrink-0 items-center gap-3 border-b border-slate-200 bg-white px-3 sm:px-6">
        <button
            type="button"
            class="rounded p-2 text-dark-blue-500 hover:bg-slate-100 lg:hidden"
            aria-label="Меню"
            @click="$emit('toggle-mobile')"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="min-w-0 flex-1 truncate text-sm font-jost-medium text-slate-500">
            <slot name="title" />
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <div class="hidden items-center gap-2 sm:flex">
                <div
                    class="flex h-8 w-8 items-center justify-center bg-pink-500 text-xs font-jost-bold text-white"
                >
                    {{ initials }}
                </div>
                <div class="leading-tight">
                    <div class="max-w-[10rem] truncate text-sm font-jost-medium text-slate-700">
                        {{ userName }}
                    </div>
                    <div
                        v-if="userEmail"
                        class="max-w-[10rem] truncate text-xs text-slate-400"
                    >
                        {{ userEmail }}
                    </div>
                </div>
            </div>
            <button
                type="button"
                class="text-sm font-jost-bold text-pink-500 hover:text-pink-600"
                @click="$emit('logout')"
            >
                Выйти
            </button>
        </div>
    </header>
</template>
