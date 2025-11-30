<script>
export default {
    name: "WorkSchedulePage",
    data() {
        return {
            schedule: [
                {
                    day: "ПОНЕДЕЛЬНИК",
                    workshop: "11:00 – 17:00 МАСТЕРСКАЯ",
                    delivery: "13:00 – 17:00 ДОСТАВКА",
                    isDayOff: false,
                },
                {
                    day: "ВТОРНИК",
                    workshop: "11:00 – 17:00 МАСТЕРСКАЯ",
                    delivery: "13:00 – 17:00 ДОСТАВКА",
                    isDayOff: false,
                },
                {
                    day: "СРЕДА",
                    workshop: "11:00 – 17:00 МАСТЕРСКАЯ",
                    delivery: "13:00 – 17:00 ДОСТАВКА",
                    isDayOff: false,
                },
                {
                    day: "ЧЕТВЕРГ",
                    isDayOff: true,
                    dayOffText: "ВСЕГДА ВЫХОДНОЙ",
                },
                {
                    day: "ПЯТНИЦА",
                    workshop: "11:00 – 17:00 МАСТЕРСКАЯ",
                    delivery: "13:00 – 17:00 ДОСТАВКА",
                    isDayOff: false,
                },
                {
                    day: "СУББОТА",
                    workshop: "11:00 – 17:00 МАСТЕРСКАЯ",
                    delivery: "13:00 – 17:00 ДОСТАВКА",
                    isDayOff: false,
                },
                {
                    day: "ВОСКРЕСЕНЬЕ",
                    isDayOff: true,
                    dayOffText: "ВСЕГДА ВЫХОДНОЙ",
                },
            ],
        };
    },
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-dark-blue-500">
        <!-- Секция заголовка -->
        <section
            class="relative overflow-hidden text-white pt-12 sm:pt-16 lg:pt-20 pb-16 sm:pb-20 lg:pb-24"
        >
            <!-- Закругленный низ секции -->
            <div
                class="absolute w-200 h-200 sm:w-200 sm:h-200 lg:w-[400px] lg:h-[400px] bg-dark-blue-500 dark:bg-white bottom-0 left-1/2 -translate-x-1/2 rounded-full"
            ></div>

            <div
                class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 relative z-10"
            >
                <h1
                    class="text-4xl sm:text-5xl lg:text-6xl font-jost-bold text-white text-center mb-8 sm:mb-12 dark:text-dark-blue-500"
                >
                    ГРАФИК РАБОТЫ
                </h1>
            </div>
        </section>

        <!-- Секция графика -->
        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div
                class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-6"
            >
                <!-- Блоки дней недели -->
                <div
                    v-for="(day, index) in schedule"
                    :key="index"
                    class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-white dark:backdrop-blur-xl"
                >
                    <!-- Название дня в верхней границе (слева) -->
                    <h2
                        class="absolute top-0 left-0 -translate-y-1/2 max-w-[75%] px-3 sm:px-4 bg-white dark:bg-white"
                    >
                        <span
                            class="text-sm sm:text-base font-jost-bold text-[#C3006B] dark:text-[#C3006B] leading-tight"
                        >
                            {{ day.day }}
                        </span>
                    </h2>

                    <div class="mt-4 space-y-3">
                        <!-- Выходной день -->
                        <p
                            v-if="day.isDayOff"
                            class="text-base sm:text-lg font-jost-bold text-[#C3006B] dark:text-[#C3006B]"
                        >
                            {{ day.dayOffText }}
                        </p>

                        <!-- Обычный день -->
                        <template v-else>
                            <p
                                class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-dark-gray-500"
                            >
                                {{ day.workshop }}
                            </p>
                            <p
                                class="text-sm sm:text-base font-jost-regular text-[#C3006B] dark:text-[#C3006B] underline"
                            >
                                {{ day.delivery }}
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped></style>
