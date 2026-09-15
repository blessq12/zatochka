<script>
import { mapState } from "pinia";
import PageHero from "../components/Layout/PageHero.vue";
import OrderServiceCta from "../components/Support/OrderServiceCta.vue";
import { useBootstrapStore } from "../stores/bootstrapStore.js";

export default {
    name: "WorkSchedulePage",
    components: {
        PageHero,
        OrderServiceCta,
    },
    computed: {
        ...mapState(useBootstrapStore, ["scheduleDays"]),
    },
    async mounted() {
        await useBootstrapStore().fetchBootstrap();
    },
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-dark-blue-500">
        <PageHero title="ГРАФИК РАБОТЫ" />

        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div
                class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 space-y-6"
            >
                <div
                    v-for="day in scheduleDays"
                    :key="day.id"
                    class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
                >
                    <h2
                        class="absolute top-0 left-0 -translate-y-1/2 max-w-[75%] px-3 sm:px-4 bg-white dark:bg-dark-blue-500"
                    >
                        <span
                            class="text-sm sm:text-base font-jost-bold text-[#C3006B] dark:text-white leading-tight"
                        >
                            {{ day.name }}
                        </span>
                    </h2>

                    <div class="mt-4 space-y-3">
                        <p
                            v-if="day.is_day_off"
                            class="text-base sm:text-lg font-jost-bold text-[#C3006B] dark:text-white"
                        >
                            {{ day.day_off_text }}
                        </p>

                        <template v-else>
                            <p
                                class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-white"
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

                <div class="pt-8">
                    <OrderServiceCta
                        with-delivery-hint
                        sharpening-label="Заточка с доставкой"
                        repair-label="Ремонт с доставкой"
                    />
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped></style>
