<script>
export default {
    name: "WorkScheduleSection",
    props: {
        days: {
            type: Array,
            required: true,
        },
    },
};
</script>

<template>
    <section
        class="bg-white/80 backdrop-blur-xl text-dark-gray-500 dark:bg-dark-blue-500/90 dark:backdrop-blur-xl dark:text-gray-100"
    >
        <div
            class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20"
        >
            <h2
                class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 text-center mb-8 sm:mb-10"
            >
                ГРАФИК РАБОТЫ
            </h2>

            <div class="space-y-6">
                <div
                    v-for="day in days"
                    :key="day.id || day.name"
                    class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-4 sm:px-6 pt-8 pb-4 bg-white/80 backdrop-blur-xl dark:bg-transparent"
                >
                    <!-- Название дня на линии обводки слева -->
                    <h3
                        class="absolute top-0 left-4 sm:left-6 -translate-y-1/2 px-3 bg-white dark:bg-dark-blue-500/90 text-lg sm:text-xl font-jost-bold uppercase tracking-wide"
                        :class="
                            day.isWeekend
                                ? 'text-[#C20A6C] dark:text-[#C20A6C]'
                                : 'text-dark-blue-500 dark:text-dark-blue-300'
                        "
                    >
                        {{ day.name }}
                    </h3>

                    <div class="space-y-2 text-sm sm:text-base">
                        <template v-if="day.isWeekend">
                            <p
                                class="font-jost-bold text-[#C20A6C] dark:text-[#C20A6C]"
                            >
                                ВСЕГДА ВЫХОДНОЙ
                            </p>
                        </template>
                        <template v-else>
                            <p
                                v-for="(line, index) in day.lines"
                                :key="index"
                                class="font-jost-regular text-dark-gray-500 dark:text-gray-200"
                            >
                                {{ line }}
                            </p>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-center">
                <router-link
                    to="/delivery"
                    class="bg-pink-500 hover:bg-pink-600 dark:bg-pink-600 dark:hover:bg-pink-700 text-white px-10 py-4 font-jost-bold text-lg tracking-wide transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1"
                >
                    ЗАКАЗАТЬ ДОСТАВКУ
                </router-link>
            </div>
        </div>
    </section>
</template>

<style scoped></style>
