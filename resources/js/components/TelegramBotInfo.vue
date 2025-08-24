<template>
    <div class="telegram-bot-info space-y-4" ref="container">
        <!-- Основная карточка -->
        <div
            class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl p-6 text-white shadow-lg"
            ref="mainCard"
        >
            <div class="text-center">
                <div
                    class="bg-white/20 rounded-full p-4 backdrop-blur-sm inline-block mb-4"
                >
                    <i class="mdi mdi-robot text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Telegram Бот</h3>
                <p class="text-blue-100 text-sm mb-4">
                    Получайте уведомления и управляйте заказами
                </p>
                <div class="mb-4">
                    <span
                        class="bg-white/20 px-3 py-2 rounded-full text-sm backdrop-blur-sm"
                    >
                        @zatochka_tsk_bot
                    </span>
                </div>
                <a
                    href="https://t.me/zatochka_tsk_bot"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transform hover:scale-105 transition-all duration-200 shadow-md"
                    ref="startChatButton"
                >
                    <i class="mdi mdi-telegram mr-2"></i>
                    Начать чат
                </a>
            </div>
        </div>

        <!-- Возможности -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-md"
            ref="featuresCard"
        >
            <h4
                class="font-semibold text-gray-900 dark:text-white mb-4 text-center"
            >
                Что умеет бот:
            </h4>
            <ul class="space-y-2">
                <li
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 p-2 rounded-lg"
                    ref="feature1"
                >
                    <i
                        class="mdi mdi-check-circle text-green-500 mr-3 text-lg"
                    ></i>
                    <span>Уведомления о статусе заказов</span>
                </li>
                <li
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 p-2 rounded-lg"
                    ref="feature2"
                >
                    <i
                        class="mdi mdi-check-circle text-green-500 mr-3 text-lg"
                    ></i>
                    <span>Верификация аккаунта</span>
                </li>
                <li
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 p-2 rounded-lg"
                    ref="feature3"
                >
                    <i
                        class="mdi mdi-check-circle text-green-500 mr-3 text-lg"
                    ></i>
                    <span>Информация о ценах и услугах</span>
                </li>
                <li
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 p-2 rounded-lg"
                    ref="feature4"
                >
                    <i
                        class="mdi mdi-check-circle text-green-500 mr-3 text-lg"
                    ></i>
                    <span>Поддержка и консультации</span>
                </li>
            </ul>
        </div>

        <!-- Инструкция -->
        <div
            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6"
            ref="instructionCard"
        >
            <h4
                class="font-semibold text-blue-900 dark:text-blue-100 mb-4 text-center"
            >
                Как подключиться:
            </h4>
            <div class="space-y-3">
                <div class="flex items-start" ref="step1">
                    <div
                        class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5"
                    >
                        1
                    </div>
                    <span class="text-sm text-blue-800 dark:text-blue-200"
                        >Нажмите "Начать чат" выше</span
                    >
                </div>
                <div class="flex items-start" ref="step2">
                    <div
                        class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5"
                    >
                        2
                    </div>
                    <span class="text-sm text-blue-800 dark:text-blue-200">
                        Отправьте команду
                        <code
                            class="bg-blue-200 dark:bg-blue-800 px-2 py-1 rounded ml-1 font-mono"
                            >/start</code
                        >
                    </span>
                </div>
                <div class="flex items-start" ref="step3">
                    <div
                        class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5"
                    >
                        3
                    </div>
                    <span class="text-sm text-blue-800 dark:text-blue-200"
                        >Укажите номер телефона для привязки</span
                    >
                </div>
                <div class="flex items-start" ref="step4">
                    <div
                        class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5"
                    >
                        4
                    </div>
                    <span class="text-sm text-blue-800 dark:text-blue-200"
                        >Получайте уведомления автоматически</span
                    >
                </div>
            </div>
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

/* Стили для номеров шагов */
.bg-blue-500 {
    min-width: 24px;
    min-height: 24px;
}
</style>
