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
    data() {
        return {
            menuOpen: false,
        };
    },
    computed: {
        displayName() {
            return String(this.userName || this.userEmail || "Профиль").trim();
        },
        initials() {
            const source = String(this.userName || this.userEmail || "").trim();
            const parts = source.split(/[\s@]+/).filter(Boolean);
            if (parts.length === 0) return "?";
            if (parts.length === 1) return parts[0].slice(0, 1).toUpperCase();
            return (parts[0][0] + parts[1][0]).toUpperCase();
        },
    },
    mounted() {
        document.addEventListener("click", this.onDocumentClick);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.onDocumentClick);
    },
    methods: {
        onDocumentClick(event) {
            if (!this.$refs.menuRoot?.contains(event.target)) {
                this.menuOpen = false;
            }
        },
        toggleMenu() {
            this.menuOpen = !this.menuOpen;
        },
        closeMenu() {
            this.menuOpen = false;
        },
        onLogout() {
            this.closeMenu();
            this.$emit("logout");
        },
    },
};
</script>

<template>
    <header
        class="sticky top-0 z-[800] flex h-12 shrink-0 items-center gap-2 border-b border-slate-200/80 bg-white/95 px-3 pt-[env(safe-area-inset-top)] backdrop-blur-sm sm:h-14 sm:gap-3 sm:px-4 lg:px-6"
    >
        <button
            type="button"
            class="shrink-0 rounded p-2 text-dark-blue-500 hover:bg-slate-100 lg:hidden"
            aria-label="Открыть меню"
            @click="$emit('toggle-mobile')"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>

        <div class="min-w-0 flex-1 truncate text-sm font-jost-medium text-dark-blue-500 sm:text-base">
            <slot name="title" />
        </div>

        <div ref="menuRoot" class="relative shrink-0">
            <button
                type="button"
                class="flex items-center gap-2 border border-slate-200 bg-white px-1.5 py-1 transition hover:border-pink-300 hover:bg-pink-50/60 sm:px-2 sm:py-1.5"
                :aria-expanded="menuOpen ? 'true' : 'false'"
                aria-haspopup="menu"
                @click="toggleMenu"
            >
                <span
                    class="flex h-8 w-8 items-center justify-center bg-pink-500 text-xs font-jost-bold text-white sm:h-9 sm:w-9"
                >
                    {{ initials }}
                </span>
                <span class="hidden min-w-0 text-left md:block">
                    <span
                        class="block max-w-[11rem] truncate text-sm font-jost-medium text-slate-700"
                    >
                        {{ displayName }}
                    </span>
                    <span
                        v-if="userEmail && userName && userEmail !== userName"
                        class="block max-w-[11rem] truncate text-xs text-slate-400"
                    >
                        {{ userEmail }}
                    </span>
                </span>
                <svg
                    class="hidden h-4 w-4 text-slate-400 transition md:block"
                    :class="menuOpen ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </button>

            <div
                v-if="menuOpen"
                class="absolute right-0 z-50 mt-2 w-[min(16rem,calc(100vw-1.5rem))] border border-slate-200 bg-white py-2 shadow-lg"
                role="menu"
            >
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="text-xs font-jost-medium uppercase tracking-wide text-slate-400">
                        Профиль
                    </p>
                    <p class="mt-1 truncate text-sm font-jost-medium text-dark-blue-500">
                        {{ displayName }}
                    </p>
                    <p
                        v-if="userEmail && userEmail !== displayName"
                        class="mt-0.5 truncate text-xs text-slate-500"
                    >
                        {{ userEmail }}
                    </p>
                </div>

                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-jost-medium text-pink-600 hover:bg-pink-50"
                    role="menuitem"
                    @click="onLogout"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"
                        />
                    </svg>
                    Выйти
                </button>
            </div>
        </div>
    </header>
</template>
