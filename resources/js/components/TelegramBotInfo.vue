<template>
    <div class="telegram-bot-info" ref="container">
        <!-- Основная карточка -->
        <div
            class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl p-6 text-white shadow-lg"
            ref="mainCard"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 rounded-full p-3 backdrop-blur-sm">
                        <i class="mdi mdi-robot text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Telegram Бот</h3>
                        <p class="text-blue-100 text-sm">
                            Получайте уведомления и управляйте заказами
                        </p>
                        <div class="mt-2">
                            <span
                                class="bg-white/20 px-2 py-1 rounded-full text-xs backdrop-blur-sm"
                            >
                                @zatochka_tsk_bot
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <a
                        href="https://t.me/zatochka_tsk_bot"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transform hover:scale-105 transition-all duration-200 shadow-md"
                        ref="startChatButton"
                    >
                        <i class="mdi mdi-telegram mr-2"></i>
                        Начать чат
                    </a>
                </div>
            </div>
        </div>

        <!-- Возможности -->
        <div
            class="mt-4 bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-md"
            ref="featuresCard"
        >
            <h4 class="font-medium text-gray-900 dark:text-white mb-3">
                Что умеет бот:
            </h4>
            <div class="space-y-2">
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                    ref="feature1"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Уведомления о статусе заказов
                </div>
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                    ref="feature2"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Верификация аккаунта
                </div>
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                    ref="feature3"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Информация о ценах и услугах
                </div>
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                    ref="feature4"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Поддержка и консультации
                </div>
            </div>
        </div>

        <!-- Инструкция -->
        <div
            class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4"
            ref="instructionCard"
        >
            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                Как подключиться:
            </h4>
            <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <li ref="step1">1. Нажмите "Начать чат" выше</li>
                <li ref="step2">
                    2. Отправьте команду
                    <code class="bg-blue-200 dark:bg-blue-800 px-1 rounded"
                        >/start</code
                    >
                </li>
                <li ref="step3">3. Укажите номер телефона для привязки</li>
                <li ref="step4">4. Получайте уведомления автоматически</li>
            </ol>
        </div>
    </div>
</template>

<script>
import { gsap } from "gsap";

export default {
    name: "TelegramBotInfo",
    mounted() {
        // Анимация появления компонента
        this.$nextTick(() => {
            this.animateComponentEnter();
        });
    },
    methods: {
        // Анимация появления компонента
        animateComponentEnter() {
            // Анимация основной карточки
            gsap.fromTo(
                this.$refs.mainCard,
                {
                    opacity: 0,
                    y: 30,
                    scale: 0.95,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.6,
                    ease: "back.out(1.7)",
                }
            );

            // Анимация карточки возможностей
            gsap.fromTo(
                this.$refs.featuresCard,
                {
                    opacity: 0,
                    y: 20,
                    scale: 0.98,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.5,
                    ease: "back.out(1.7)",
                    delay: 0.2,
                }
            );

            // Анимация карточки инструкции
            gsap.fromTo(
                this.$refs.instructionCard,
                {
                    opacity: 0,
                    y: 20,
                    scale: 0.98,
                },
                {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.5,
                    ease: "back.out(1.7)",
                    delay: 0.4,
                }
            );

            // Анимация кнопки
            gsap.fromTo(
                this.$refs.startChatButton,
                {
                    opacity: 0,
                    scale: 0.8,
                },
                {
                    opacity: 1,
                    scale: 1,
                    duration: 0.4,
                    ease: "back.out(1.7)",
                    delay: 0.6,
                }
            );

            // Анимация возможностей по очереди
            const features = [
                this.$refs.feature1,
                this.$refs.feature2,
                this.$refs.feature3,
                this.$refs.feature4,
            ];

            features.forEach((feature, index) => {
                gsap.fromTo(
                    feature,
                    {
                        opacity: 0,
                        x: -20,
                    },
                    {
                        opacity: 1,
                        x: 0,
                        duration: 0.3,
                        ease: "power2.out",
                        delay: 0.8 + index * 0.1,
                    }
                );
            });

            // Анимация шагов инструкции
            const steps = [
                this.$refs.step1,
                this.$refs.step2,
                this.$refs.step3,
                this.$refs.step4,
            ];

            steps.forEach((step, index) => {
                gsap.fromTo(
                    step,
                    {
                        opacity: 0,
                        x: -15,
                    },
                    {
                        opacity: 1,
                        x: 0,
                        duration: 0.3,
                        ease: "power2.out",
                        delay: 1.2 + index * 0.1,
                    }
                );
            });
        },
    },
};
</script>

<style scoped>
/* Дополнительные стили для анимаций */
.telegram-bot-info {
    transition: all 0.3s ease;
}

/* Эффект hover для кнопки */
a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Анимация для иконок */
.mdi {
    transition: all 0.2s ease;
}

/* Эффект для карточек при hover */
.bg-white:hover,
.bg-blue-50:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.dark .bg-gray-800:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}
</style>
