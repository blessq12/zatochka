<script>
import { mapState } from "pinia";
import PageHero from "../components/Layout/PageHero.vue";
import { useBootstrapStore } from "../stores/bootstrapStore.js";

export default {
    name: "DeliveryPage",
    components: {
        PageHero,
    },
    computed: {
        ...mapState(useBootstrapStore, [
            "freeDeliveryConditions",
            "deliveryAdvantages",
        ]),
    },
    async mounted() {
        await useBootstrapStore().fetchBootstrap();
    },
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-dark-blue-500">
        <PageHero title="ДОСТАВКА" />

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

                <div
                    class="px-6 pt-6 pb-6 sm:px-10 sm:pt-8 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl space-y-6"
                >
                    <div class="flex items-center justify-start gap-4">
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

                <div class="flex justify-center pt-4">
                    <button
                        class="bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl"
                        id="low"
                    >
                        ОСТАВИТЬ ЗАЯВКУ
                    </button>
                </div>

                <div
                    class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl mt-24"
                >
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
                            v-for="(advantage, index) in deliveryAdvantages"
                            :key="index"
                            class="flex items-start gap-3"
                        >
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
