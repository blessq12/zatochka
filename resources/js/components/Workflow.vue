<script>
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
export default {
    name: "Workflow",
    data() {
        return {
            workflowSteps: [
                {
                    id: 1,
                    title: "Диагностика",
                    description:
                        "Осмотр инструментов, сбор жалоб, определение работ",
                    icon: "🔍",
                    color: "blue",
                },
                {
                    id: 2,
                    title: "Согласование",
                    description: "Согласование ремонта аппаратов",
                    icon: "🤝",
                    color: "pink",
                },
                {
                    id: 3,
                    title: "Выполнение",
                    description:
                        "Работа по современным протоколам с профессиональным оборудованием",
                    icon: "⚙️",
                    color: "light-pink",
                },
                {
                    id: 4,
                    title: "Контроль качества",
                    description:
                        "Тестирование заточки (претензии в течение 3 дней), прокатка аппарата на холостом ходу",
                    icon: "✅",
                    color: "dark-blue",
                },
                {
                    id: 5,
                    title: "Упаковка",
                    description:
                        "Тщательная упаковка для сохранности при транспортировке",
                    icon: "📦",
                    color: "green",
                },
                {
                    id: 6,
                    title: "Доставка",
                    description: "Бережная доставка, курьер проинструктирован",
                    icon: "🚚",
                    color: "purple",
                },
            ],
        };
    },
    computed: {
        firstRowSteps() {
            return this.workflowSteps.slice(0, 3);
        },
        secondRowSteps() {
            return this.workflowSteps.slice(3, 6);
        },
    },
    mounted() {
        gsap.registerPlugin(ScrollTrigger);
        this.animateCards();
    },
    methods: {
        animateCards() {
            // Анимация с ScrollTrigger - запускается только когда элемент в области видимости
            gsap.fromTo(
                ".workflow-card",
                {
                    x: -100,
                    opacity: 0,
                },
                {
                    x: 0,
                    opacity: 1,
                    duration: 0.8,
                    ease: "power2.out",
                    stagger: 0.5, // задержка в полсекунды между карточками
                    scrollTrigger: {
                        trigger: ".workflow-container",
                        start: "top 80%", // анимация начинается когда верх элемента на 80% от верха экрана
                        end: "bottom 20%",
                        toggleActions: "play none none reverse", // играть при входе, не делать ничего при выходе, не играть при повторном входе, играть в обратную сторону при выходе
                    },
                }
            );
        },
    },
};
</script>

<template>
    <div class="workflow-container space-y-12">
        <!-- Первая строка: шаги 1-3 -->
        <div
            class="flex flex-col lg:flex-row lg:justify-between gap-8 lg:gap-4"
        >
            <div
                v-for="step in firstRowSteps"
                :key="step.id"
                class="flex-1 text-center group"
            >
                <div
                    class="workflow-card bg-white/90 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:bg-gray-800/90 dark:border-gray-600/30 group-hover:shadow-2xl group-hover:scale-105 transition-all duration-300 h-full flex flex-col"
                >
                    <!-- Нумерация в левом верхнем углу -->
                    <div
                        :class="[
                            'absolute top-4 left-4 text-2xl font-jost-bold',
                            `text-${step.color}-500/60 dark:text-${step.color}-400/60`,
                        ]"
                    >
                        {{ step.id }}
                    </div>

                    <div
                        :class="[
                            'w-16 h-16 rounded-3xl flex items-center justify-center mx-auto mb-4 mt-2',
                            `bg-${step.color}-500/20`,
                        ]"
                    >
                        <span class="text-3xl">{{ step.icon }}</span>
                    </div>
                    <h3
                        class="text-lg font-jost-bold text-dark-gray-500 mb-2 dark:text-gray-100"
                    >
                        {{ step.title }}
                    </h3>
                    <p
                        class="text-sm text-gray-500 font-jost-regular dark:text-gray-400 flex-grow"
                    >
                        {{ step.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Вторая строка: шаги 4-6 -->
        <div
            class="flex flex-col lg:flex-row lg:justify-between gap-8 lg:gap-4"
        >
            <div
                v-for="step in secondRowSteps"
                :key="step.id"
                class="flex-1 text-center group"
            >
                <div
                    class="workflow-card bg-white/90 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:bg-gray-800/90 dark:border-gray-600/30 group-hover:shadow-2xl group-hover:scale-105 transition-all duration-300 h-full flex flex-col"
                >
                    <!-- Нумерация в левом верхнем углу -->
                    <div
                        :class="[
                            'absolute top-4 left-4 text-2xl font-jost-bold',
                            `text-${step.color}-500/60 dark:text-${step.color}-400/60`,
                        ]"
                    >
                        {{ step.id }}
                    </div>

                    <div
                        :class="[
                            'w-16 h-16 rounded-3xl flex items-center justify-center mx-auto mb-4 mt-2',
                            `bg-${step.color}-500/20`,
                        ]"
                    >
                        <span class="text-3xl">{{ step.icon }}</span>
                    </div>
                    <h3
                        class="text-lg font-jost-bold text-dark-gray-500 mb-2 dark:text-gray-100"
                    >
                        {{ step.title }}
                    </h3>
                    <p
                        class="text-sm text-gray-500 font-jost-regular dark:text-gray-400 flex-grow"
                    >
                        {{ step.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
