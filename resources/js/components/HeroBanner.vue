<script>
import { gsap } from "gsap";

export default {
    name: "HeroBanner",
    props: {
        routes: {
            type: Object,
            default: () => ({
                sharpening: "/sharpening",
                repair: "/repair",
                delivery: "/delivery",
                contacts: "/contacts",
            }),
        },
    },
    data() {
        return {
            achievements: [
                "Восстановили 30 000+ инструментов: ножницы, кусачки, пинцеты, машинки",
                "Бесплатная доставка при заказе от 6 маникюрных или 3 парикмахерских/грумерских инструментов",
            ],
        };
    },
    mounted() {
        this.animateIn();
    },
    methods: {
        animateIn() {
            // Создаем timeline для последовательной анимации
            const tl = gsap.timeline({ ease: "power3.out" });

            // Анимация бейджа
            tl.from(this.$refs.badge, {
                y: -50,
                opacity: 0,
                duration: 0.8,
                ease: "back.out(1.7)",
            });

            // Анимация заголовка
            tl.from(
                this.$refs.title,
                {
                    y: 50,
                    opacity: 0,
                    duration: 0.8,
                    ease: "power3.out",
                },
                "-=0.4"
            );

            // Анимация описания
            tl.from(
                this.$refs.description,
                {
                    y: 30,
                    opacity: 0,
                    duration: 0.6,
                    ease: "power2.out",
                },
                "-=0.3"
            );

            // Анимация достижений
            this.achievements.forEach((_, index) => {
                const element = this.$refs[`achievement-${index}`];
                if (element && element[0]) {
                    tl.from(
                        element[0],
                        {
                            x: -30,
                            opacity: 0,
                            duration: 0.5,
                            ease: "power2.out",
                        },
                        "-=0.2"
                    );
                }
            });

            // Анимация гарантий
            tl.from(
                this.$refs.guarantees,
                {
                    y: 30,
                    opacity: 0,
                    duration: 0.6,
                    ease: "power2.out",
                },
                "-=0.3"
            );

            // Анимация кнопок
            tl.from(
                this.$refs.buttons,
                {
                    y: 30,
                    opacity: 0,
                    duration: 0.6,
                    ease: "power2.out",
                },
                "-=0.3"
            );

            // Анимация формы
            tl.from(
                this.$refs.form,
                {
                    x: 100,
                    opacity: 0,
                    duration: 0.8,
                    ease: "power3.out",
                },
                "-=0.6"
            );

            // Анимация скролл индикатора
            tl.from(
                this.$refs.scrollIndicator,
                {
                    y: 30,
                    opacity: 0,
                    duration: 0.6,
                    ease: "power2.out",
                },
                "-=0.4"
            );

            // Добавляем hover анимации для кнопок
            this.addButtonHoverAnimations();
        },

        addButtonHoverAnimations() {
            const buttons = this.$refs.buttons.querySelectorAll("a");
            buttons.forEach((button) => {
                button.addEventListener("mouseenter", () => {
                    gsap.to(button, {
                        scale: 1.05,
                        duration: 0.3,
                        ease: "power2.out",
                    });
                });

                button.addEventListener("mouseleave", () => {
                    gsap.to(button, {
                        scale: 1,
                        duration: 0.3,
                        ease: "power2.out",
                    });
                });
            });
        },

        scrollToNextSection() {
            const nextSection = document.querySelector(
                "section:nth-of-type(2)"
            );
            if (nextSection) {
                nextSection.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        },
    },
};
</script>

<template>
    <section
        class="relative bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 py-24 overflow-hidden"
    >
        <!-- Фоновые элементы -->
        <div class="absolute inset-0">
            <div
                class="absolute top-0 left-0 w-72 h-72 bg-accent/5 rounded-full blur-3xl"
            ></div>
            <div
                class="absolute bottom-0 right-0 w-96 h-96 bg-accent/5 rounded-full blur-3xl"
            ></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Левая колонка с контентом -->
                <div class="space-y-8">
                    <!-- Бейдж -->
                    <div
                        ref="badge"
                        class="inline-block px-4 py-2 bg-accent/10 dark:bg-accent/20 rounded-full"
                    >
                        <span
                            class="text-sm font-bold text-accent dark:text-accent-light"
                        >
                            ОСТРЫЕ ИНСТРУМЕНТЫ ЗА 2 ДНЯ
                        </span>
                    </div>

                    <!-- Заголовок -->
                    <div ref="title" class="space-y-4">
                        <h1
                            class="text-5xl lg:text-7xl font-black text-gray-900 dark:text-white leading-tight"
                        >
                            ЗАТОЧКА
                            <span class="text-accent dark:text-accent-light"
                                >.ТСК</span
                            >
                        </h1>
                    </div>

                    <!-- Описание -->
                    <div ref="description" class="space-y-6">
                        <p
                            class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed"
                        >
                            Более 5 лет затачиваем и ремонтируем инструменты для
                            мастеров маникюра, парикмахеров, грумеров и
                            лешмейкеров.
                        </p>

                        <!-- Список достижений -->
                        <ul class="space-y-3">
                            <li
                                v-for="(achievement, index) in achievements"
                                :key="index"
                                :ref="`achievement-${index}`"
                                class="flex items-start space-x-3"
                            >
                                <div
                                    class="flex-shrink-0 w-2 h-2 bg-accent dark:bg-accent-light rounded-full mt-2"
                                ></div>
                                <span
                                    class="text-gray-700 dark:text-gray-300"
                                    >{{ achievement }}</span
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Гарантии -->
                    <div ref="guarantees" class="space-y-4">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            Гарантия:
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <div
                                class="px-4 py-3 bg-accent/5 dark:bg-accent/10 rounded-lg border border-accent/20"
                            >
                                <span
                                    class="font-bold text-accent dark:text-accent-light"
                                    >на заточку:</span
                                >
                                <span
                                    class="text-gray-700 dark:text-gray-300 ml-2"
                                    >3 дня</span
                                >
                            </div>
                            <div
                                class="px-4 py-3 bg-accent/5 dark:bg-accent/10 rounded-lg border border-accent/20"
                            >
                                <span
                                    class="font-bold text-accent dark:text-accent-light"
                                    >на ремонт:</span
                                >
                                <span
                                    class="text-gray-700 dark:text-gray-300 ml-2"
                                    >6 месяцев</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div ref="buttons" class="flex flex-col sm:flex-row gap-4">
                        <a
                            href="#order-form"
                            class="inline-flex items-center justify-center px-8 py-4 bg-accent hover:bg-accent/90 dark:bg-accent-light dark:hover:bg-accent-light/90 text-white font-bold rounded-lg transition-all duration-300 transform hover:scale-105 hover:shadow-lg"
                        >
                            <i class="mdi mdi-scissors-cutting mr-2"></i>
                            Заказать заточку
                        </a>
                        <a
                            :href="routes.repair"
                            class="inline-flex items-center justify-center px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-lg transition-all duration-300 transform hover:scale-105 hover:shadow-lg"
                        >
                            <i class="mdi mdi-tools mr-2"></i>
                            Заказать ремонт
                        </a>
                    </div>
                </div>

                <!-- Правая колонка с формой -->
                <div ref="form" class="relative">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-200 dark:border-gray-700"
                    >
                        <hero-form
                            header="Заказать доставку"
                            description="Ознакомлен с условиями доставки: ≥6 маникюрных или ≥3 парикмахерских/грумерских/барберских инструментов для бесплатной доставки"
                        />
                    </div>
                </div>
            </div>

            <!-- Скролл индикатор -->
            <div ref="scrollIndicator" class="flex justify-center mt-16">
                <button
                    @click="scrollToNextSection"
                    class="group flex flex-col items-center space-y-3 text-gray-600 dark:text-gray-400 hover:text-accent dark:hover:text-accent-light transition-all duration-300"
                >
                    <span class="font-medium text-sm"> Узнай подробнее </span>
                    <div class="relative">
                        <div
                            class="w-8 h-8 border-2 border-current rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                        >
                            <i
                                class="mdi mdi-chevron-down text-lg animate-bounce"
                            ></i>
                        </div>
                        <!-- Пульсирующий круг -->
                        <div
                            class="absolute inset-0 w-8 h-8 border-2 border-current rounded-full animate-ping opacity-20"
                        ></div>
                    </div>
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Дополнительные стили для анимаций */
.animate-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%,
    20%,
    50%,
    80%,
    100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

/* Градиентный фон */
.bg-gradient-to-br {
    background: linear-gradient(135deg, var(--tw-gradient-stops));
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
