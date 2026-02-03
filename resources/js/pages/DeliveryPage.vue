<script>
import PageHero from "../components/Layout/PageHero.vue";

export default {
    name: "DeliveryPage",
    components: {
        PageHero,
    },
    data() {
        return {
            freeDeliveryConditions: [
                "От 6 маникюрных инструментов",
                "От 3 парикмахерских/грумерских/барберских инструментов",
                "Любой аппарат в ремонт",
            ],
            advantages: [
                {
                    title: "Безопасная упаковка",
                    description:
                        "Используем специальную упаковку для защиты ваших инструментов",
                },
                {
                    title: "Гарантия качества",
                    description:
                        "Несем ответственность за сохранность ваших инструментов",
                },
                {
                    title: "Курьер забирает заказ",
                    description:
                        "с 13:00 до 17:00 часов в дни работы мастерской (пн, вт, ср, пт, сб) чт и вс всегда выходной.",
                },
            ],
        };
    },
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-dark-blue-500">
        <!-- Секция заголовка -->
        <PageHero title="ДОСТАВКА" />

        <!-- Секция контента -->
        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div
                class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-10"
            >
                <div class="flex justify-center pt-4">
                    <button
                        class="bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-10 py-8 w-full font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                        id="high"
                    >
                        Заказать
                    </button>
                </div>

                <!-- Блок ДОСТАВКА -->
                <div
                    class="px-6 pt-6 pb-6 sm:px-10 sm:pt-8 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl space-y-6"
                >
                    <div class="flex items-center justify-start gap-4">
                        <svg
                            width="44"
                            height="40"
                            viewBox="0 0 44 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M11.9765 20.7872C12.4313 21.25 13.0457 21.4973 13.692 21.4973C14.3383 21.4973 14.9527 21.242 15.4076 20.7872L24.7832 11.4115L23.9693 10.5977L14.5937 19.9733C14.3543 20.2127 14.0351 20.3483 13.692 20.3483C13.3489 20.3483 13.0138 20.2127 12.7664 19.9573L6.73411 14.3L5.95215 15.1379L11.9685 20.7792L11.9765 20.7872Z"
                                fill="#C3006B"
                            />
                            <path
                                d="M34.7975 40C37.6621 40 39.992 37.6701 39.992 34.8055C39.992 34.231 39.8883 33.6485 39.6808 33.074L39.5133 32.6032H43.6944V17.0517C43.5986 12.7349 40.0638 9.24796 35.7231 9.24796H30.7441V2.8087C30.7441 1.25274 29.4833 0 27.9354 0H2.7688C1.23678 0 0 1.23678 0 2.7688V32.6032H4.18113L4.01357 33.074C3.8061 33.6485 3.70237 34.231 3.70237 34.8055C3.70237 37.6701 6.03232 40 8.89687 40C11.7614 40 14.0914 37.6701 14.0914 34.8055C14.0914 34.231 13.9876 33.6485 13.7802 33.074L13.6126 32.6032H30.0818L29.9142 33.074C29.7068 33.6485 29.603 34.231 29.603 34.8055C29.603 37.6701 31.933 40 34.7975 40ZM8.89687 38.859C6.66268 38.859 4.84341 37.0397 4.84341 34.8055C4.84341 34.1352 5.05087 33.449 5.45781 32.7708L5.56154 32.5952H12.2402L12.3439 32.7708C12.7508 33.449 12.9583 34.1352 12.9583 34.8055C12.9583 37.0397 11.139 38.859 8.90485 38.859H8.89687ZM29.603 31.1031V31.4542H1.14103V2.80072C1.14103 1.8831 1.8831 1.14103 2.80072 1.14103H27.9274C28.853 1.14103 29.611 1.89108 29.611 2.82466V31.1031H29.603ZM30.7441 20.004V10.397H35.755C39.5053 10.397 42.5534 13.4371 42.5534 17.1953V20.3551H30.7441V20.004ZM30.7441 31.1031V21.4961H42.5534V31.4542H30.7441V31.1031ZM31.3585 32.7708L31.4622 32.5952H38.1408L38.2446 32.7708C38.6515 33.449 38.859 34.1352 38.859 34.8055C38.859 37.0397 37.0397 38.859 34.8055 38.859C32.5713 38.859 30.752 37.0397 30.752 34.8055C30.752 34.1352 30.9595 33.449 31.3664 32.7708H31.3585Z"
                                fill="#C3006B"
                            />
                        </svg>

                        <h3
                            class="text-lg sm:text-xl font-jost-bold text-[#C3006B] dark:text-[#C3006B]"
                        >
                            ДОСТАВКА
                        </h3>
                    </div>

                    <div class="space-y-3">
                        <p
                            v-for="(condition, index) in freeDeliveryConditions"
                            :key="index"
                            class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200 text-start"
                        >
                            {{ condition }}
                        </p>
                    </div>
                </div>

                <!-- Кнопка ОСТАВИТЬ ЗАЯВКУ -->
                <div class="flex justify-center pt-4">
                    <button
                        class="bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                        id="low"
                    >
                        ОСТАВИТЬ ЗАЯВКУ
                    </button>
                </div>

                <!-- Блок ПРЕИМУЩЕСТВА НАШЕЙ ДОСТАВКИ -->
                <div
                    class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl mt-24"
                >
                    <!-- Заголовок -->
                    <h2
                        class="absolute top-0 left-0 -translate-y-1/2 max-w-[75%] px-3 sm:px-4 bg-white dark:bg-dark-blue-500"
                    >
                        <span
                            class="text-sm sm:text-base font-jost-bold text-[#C3006B] dark:text-[#C3006B] leading-tight"
                        >
                            ПРЕИМУЩЕСТВА НАШЕЙ ДОСТАВКИ
                        </span>
                    </h2>

                    <div class="space-y-6 mt-4">
                        <div
                            v-for="(advantage, index) in advantages"
                            :key="index"
                            class="flex items-start gap-3"
                        >
                            <svg
                                viewBox="0 0 15 15"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 flex-shrink-0 mt-1"
                            >
                                <path
                                    d="M12.9302 0L5.22023 8.48L2.98023 6.78H1.74023L5.22023 12.43L14.1702 0H12.9302Z"
                                    fill="#C20A6C"
                                />
                                <path
                                    d="M11.94 12.43C11.94 12.57 11.83 12.68 11.69 12.68H1.74C1.6 12.68 1.49 12.57 1.49 12.43V2.49C1.49 2.35 1.6 2.24 1.74 2.24H8.54L9.9 0.75H0.5C0.23 0.75 0 0.97 0 1.25V13.68C0 13.96 0.22 14.18 0.5 14.18H12.93C13.2 14.18 13.43 13.96 13.43 13.68V4.01L11.94 6.08V12.43Z"
                                    fill="#C20A6C"
                                />
                            </svg>

                            <div>
                                <p
                                    class="text-sm sm:text-base font-jost-bold text-dark-gray-500 dark:text-gray-200"
                                >
                                    {{ advantage.title }}
                                </p>
                                <p
                                    class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 mt-1"
                                >
                                    {{ advantage.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
#high {
    animation: pulseHigh infinite ease-in-out 2s;
}

#low {
    animation: pulseLow infinite ease-in-out 2.5s;
}

@keyframes pulseHigh {
    0% {
        transform: scale(1);
        filter: brightness(1);
    }
    50% {
        transform: scale(1.02);
        filter: brightness(1.15);
    }
    100% {
        transform: scale(1);
        filter: brightness(1);
    }
}

@keyframes pulseLow {
    0% {
        transform: scale(1);
        filter: brightness(1);
    }
    50% {
        transform: scale(1.02);
        filter: brightness(1.1);
    }
    100% {
        transform: scale(1);
        filter: brightness(1);
    }
}
</style>
