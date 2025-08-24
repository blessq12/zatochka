<template>
    <a
        :href="href"
        class="relative w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 hover:border-accent dark:hover:border-accent-light transition-all duration-300 ease-out hover:scale-110 focus:outline-none focus:ring-4 focus:ring-accent/20 dark:focus:ring-accent-light/20"
        :class="{ 'border-accent dark:border-accent-light': isActive }"
        :title="getTooltipText()"
        ref="profileLink"
    >
        <!-- Иконка профиля -->
        <div class="absolute inset-0 flex items-center justify-center">
            <svg
                class="w-6 h-6 text-gray-600 dark:text-gray-300 transition-colors duration-300"
                :class="{ 'text-accent dark:text-accent-light': isActive }"
                fill="currentColor"
                viewBox="0 0 24 24"
                ref="profileIcon"
            >
                <path
                    d="M12 12a5 5 0 110-10 5 5 0 010 10zM12 14a8 8 0 00-8 8v2h16v-2a8 8 0 00-8-8z"
                />
            </svg>
        </div>

        <!-- Индикатор статуса -->
        <div
            class="absolute -top-1 -right-1 w-4 h-4 rounded-full"
            :class="statusClasses"
            ref="statusIndicator"
        >
            <div
                v-if="isAuthenticated"
                class="absolute inset-0 rounded-full animate-ping"
                :class="statusClasses"
                ref="statusPulse"
            ></div>
        </div>

        <!-- Фоновые частицы для анимации -->
        <div class="absolute inset-0 overflow-hidden rounded-full">
            <div
                v-for="i in 4"
                :key="i"
                class="absolute w-1 h-1 bg-accent dark:bg-accent-light rounded-full opacity-0"
                :style="{
                    left: `${15 + i * 15}%`,
                    top: `${15 + i * 10}%`,
                    animationDelay: `${i * 0.2}s`,
                }"
                :class="isAuthenticated ? 'animate-pulse' : ''"
            ></div>
        </div>

        <!-- Подчеркивание для активного состояния -->
        <div
            v-if="isActive"
            class="absolute -bottom-2 left-0 right-0 h-0.5 bg-accent dark:bg-accent-light transition-all duration-300"
            ref="underline"
        ></div>
    </a>
</template>

<script>
import { useAuthStore } from "../stores/auth.js";

export default {
    name: "user-profile-toggle",
    props: {
        href: {
            type: String,
            required: true,
        },
        isActive: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            isHovered: false,
        };
    },
    computed: {
        authStore() {
            return useAuthStore();
        },
        isAuthenticated() {
            return this.authStore.isAuthenticated;
        },
        client() {
            return this.authStore.getUser;
        },
        statusClasses() {
            return this.isAuthenticated
                ? "bg-green-500 dark:bg-green-400"
                : "bg-gray-400 dark:bg-gray-500";
        },
    },
    async mounted() {
        // Проверяем статус авторизации при загрузке
        await this.checkAuthStatus();

        // Добавляем анимацию при первом рендере
        this.$nextTick(() => {
            this.animateInitialLoad();
        });
    },
    beforeDestroy() {
        // Убираем слушатели событий если есть
    },
    methods: {
        async checkAuthStatus() {
            try {
                if (this.authStore.isAuthenticated) {
                    await this.authStore.checkToken();
                }
            } catch (error) {
                // Ошибка уже обработана в сторе
            }
        },

        animateClick() {
            // Анимация нажатия
            const link = this.$refs.profileLink;
            if (link) {
                link.style.transform = "scale(0.95)";
                setTimeout(() => {
                    link.style.transform = "";
                }, 150);
            }
        },

        animateInitialLoad() {
            // Анимация при первой загрузке
            const link = this.$refs.profileLink;
            if (link) {
                link.style.opacity = "0";
                link.style.transform = "scale(0.8) rotate(180deg)";

                setTimeout(() => {
                    link.style.transition =
                        "all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)";
                    link.style.opacity = "1";
                    link.style.transform = "scale(1) rotate(0deg)";
                }, 100);
            }
        },

        getTooltipText() {
            if (this.isAuthenticated && this.client) {
                return `Профиль: ${this.client.full_name || "Пользователь"}`;
            } else {
                return "Войти в систему";
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

@keyframes ping {
    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Плавные переходы для элементов */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Эффект свечения при hover */
a:hover {
    filter: drop-shadow(0 0 4px rgba(245, 0, 87, 0.3));
}

/* Дополнительные эффекты для темной темы */
.dark a:hover {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

/* Эффект ripple при клике */
a:active {
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
