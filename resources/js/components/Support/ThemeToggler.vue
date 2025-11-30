<script>
export default {
    name: "ThemeToggler",
    inject: ["themeTogglerService"],
    data() {
        return {
            isDark: false,
        };
    },
    mounted() {
        this.updateThemeState();
    },
    methods: {
        toggleTheme() {
            this.themeTogglerService.toggleTheme();
            this.updateThemeState();
        },
        updateThemeState() {
            this.isDark = document.documentElement.classList.contains("dark");
        },
    },
};
</script>

<template>
    <button
        @click="toggleTheme"
        class="relative w-10 h-10 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-white/50 rounded-lg transition-all duration-300"
        :title="
            isDark
                ? 'Переключить на светлую тему'
                : 'Переключить на темную тему'
        "
    >
        <!-- Солнце для переключения на светлую тему (когда темная активна) -->
        <svg
            v-if="isDark"
            class="w-6 h-6 text-white transition-all duration-300 ease-out hover:animate-spin"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>

        <!-- Луна для переключения на темную тему (когда светлая активна) -->
        <svg
            v-else
            class="w-6 h-6 text-black transition-all duration-300 ease-out hover:animate-pulse"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>
</template>

<style scoped>
/* Дополнительные эффекты свечения для фирменных цветов */
button:hover svg:first-child {
    filter: drop-shadow(0 0 8px rgba(195, 0, 107, 0.6));
}

:global(.dark) button:hover svg:first-child {
    filter: drop-shadow(0 0 8px rgba(233, 145, 189, 0.6));
}

button:hover svg:last-child {
    filter: drop-shadow(0 0 8px rgba(60, 60, 59, 0.6));
}

:global(.dark) button:hover svg:last-child {
    filter: drop-shadow(0 0 8px rgba(211, 211, 211, 0.6));
}
</style>
