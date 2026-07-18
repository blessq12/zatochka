<script>
import { formatPriceItem } from "../../utils/formatPriceItem.js";

export default {
    name: "ServicePriceList",
    props: {
        prices: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
        formatPriceItem,
    },
};
</script>

<template>
    <div
        class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 sm:px-10 sm:py-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
    >
        <div class="space-y-4">
            <div
                v-for="(item, itemIndex) in prices"
                :key="itemIndex"
                class="flex justify-between items-center gap-2 sm:gap-4"
            >
                <div class="flex-1 min-w-0">
                    <p
                        class="text-base sm:text-lg font-jost-regular text-dark-gray-500 dark:text-white"
                    >
                        {{ item.name }}
                    </p>
                    <p
                        v-if="item.description"
                        class="text-xs sm:text-sm font-jost-regular text-dark-gray-400 dark:text-gray-300 mt-1"
                    >
                        {{ item.description }}
                    </p>
                </div>
                <p
                    v-if="item.price"
                    class="text-lg sm:text-xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] flex-shrink-0"
                >
                    {{ formatPriceItem(item) }}
                </p>
            </div>
        </div>
    </div>
</template>
