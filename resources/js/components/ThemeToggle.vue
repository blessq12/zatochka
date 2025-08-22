<template>
    <button
        @click="toggleTheme"
        class="relative w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 hover:border-accent dark:hover:border-accent-light transition-all duration-300 ease-out hover:scale-110 focus:outline-none focus:ring-4 focus:ring-accent/20 dark:focus:ring-accent-light/20"
        :title="
            isDark
                ? 'Переключить на светлую тему'
                : 'Переключить на темную тему'
        "
        ref="toggleButton"
    >
        <!-- Солнце (светлая тема) -->
        <div
            class="absolute inset-0 flex items-center justify-center transition-all duration-500 ease-out"
            :class="
                isDark
                    ? 'opacity-0 rotate-90 scale-75'
                    : 'opacity-100 rotate-0 scale-100'
            "
        >
            <svg
                class="w-6 h-6 text-yellow-500"
                fill="currentColor"
                viewBox="0 0 24 24"
                ref="sunIcon"
            >
                <path
                    d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"
                />
            </svg>
        </div>

        <!-- Луна (темная тема) -->
        <div
            class="absolute inset-0 flex items-center justify-center transition-all duration-500 ease-out"
            :class="
                isDark
                    ? 'opacity-100 rotate-0 scale-100'
                    : 'opacity-0 -rotate-90 scale-75'
            "
        >
            <svg
                class="w-6 h-6 text-blue-400"
                fill="currentColor"
                viewBox="0 0 24 24"
                ref="moonIcon"
            >
                <path
                    d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"
                />
            </svg>
        </div>

        <!-- Фоновые частицы для анимации -->
        <div class="absolute inset-0 overflow-hidden rounded-full">
            <div
                v-for="i in 6"
                :key="i"
                class="absolute w-1 h-1 bg-accent dark:bg-accent-light rounded-full opacity-0"
                :style="{
                    left: `${20 + i * 10}%`,
                    top: `${20 + i * 5}%`,
                    animationDelay: `${i * 0.1}s`,
                }"
                :class="isDark ? 'animate-pulse' : ''"
            ></div>
        </div>
    </button>
</template>

<script>
export default {
    name: "ThemeToggle",
    data() {
        return {
            isDark: false,
        };
    },
    mounted() {
        // Проверяем сохраненную тему или системную
        const savedTheme = localStorage.getItem("theme");
        const systemPrefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)"
        ).matches;

        if (savedTheme === "dark" || (!savedTheme && systemPrefersDark)) {
            this.enableDarkMode();
        } else {
            this.disableDarkMode();
        }

        // Добавляем анимацию при первом рендере
        this.$nextTick(() => {
            this.animateInitialLoad();
        });
    },
    methods: {
        toggleTheme() {
            // Добавляем анимацию клика
            this.animateClick();

            if (this.isDark) {
                this.disableDarkMode();
            } else {
                this.enableDarkMode();
            }
        },
        enableDarkMode() {
            document.documentElement.classList.add("dark");
            localStorage.setItem("theme", "dark");
            this.isDark = true;
            this.animateThemeChange();
        },
        disableDarkMode() {
            document.documentElement.classList.remove("dark");
            localStorage.setItem("theme", "light");
            this.isDark = false;
            this.animateThemeChange();
        },
        animateClick() {
            // Анимация нажатия
            const button = this.$refs.toggleButton;
            if (button) {
                button.style.transform = "scale(0.95)";
                setTimeout(() => {
                    button.style.transform = "";
                }, 150);
            }
        },
        animateThemeChange() {
            // Анимация смены темы
            const sunIcon = this.$refs.sunIcon;
            const moonIcon = this.$refs.moonIcon;

            if (sunIcon && moonIcon) {
                if (this.isDark) {
                    // Анимация перехода к темной теме
                    sunIcon.style.transform = "rotate(180deg) scale(0.8)";
                    moonIcon.style.transform = "rotate(-180deg) scale(1.2)";
                } else {
                    // Анимация перехода к светлой теме
                    moonIcon.style.transform = "rotate(180deg) scale(0.8)";
                    sunIcon.style.transform = "rotate(-180deg) scale(1.2)";
                }

                setTimeout(() => {
                    sunIcon.style.transform = "";
                    moonIcon.style.transform = "";
                }, 500);
            }
        },
        animateInitialLoad() {
            // Анимация при первой загрузке
            const button = this.$refs.toggleButton;
            if (button) {
                button.style.opacity = "0";
                button.style.transform = "scale(0.8) rotate(180deg)";

                setTimeout(() => {
                    button.style.transition =
                        "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)";
                    button.style.opacity = "1";
                    button.style.transform = "scale(1) rotate(0deg)";
                }, 100);
            }
        },
    },
};
</script>

<style scoped>
/* Кастомные анимации */
@keyframes pulse {
    0%,
    100% {
        opacity: 0;
        transform: scale(0.5);
    }
    50% {
        opacity: 0.6;
        transform: scale(1);
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Плавные переходы для иконок */
svg {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Эффект свечения при hover */
button:hover svg {
    filter: drop-shadow(0 0 8px currentColor);
}

/* Анимация для частиц */
@keyframes float {
    0%,
    100% {
        transform: translateY(0px) scale(1);
        opacity: 0;
    }
    50% {
        transform: translateY(-10px) scale(1.2);
        opacity: 0.8;
    }
}

/* Дополнительные эффекты для темной темы */
.dark button:hover {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

/* Эффект ripple при клике */
button:active {
    transform: scale(0.95);
}

/* Анимация загрузки */
@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.8) rotate(180deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

/* Кастомные цвета для акцента */
:root {
    --color-accent: #f50057;
    --color-accent-light: #ff80ab;
}

.dark {
    --color-accent: #ff80ab;
    --color-accent-light: #f50057;
}
</style>
