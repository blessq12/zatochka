<script>
/**
 * Primary conversion CTAs: sharpening / repair order forms.
 */
export default {
    name: "OrderServiceCta",
    props: {
        /** Stack vertically (default) or side-by-side on sm+ */
        layout: {
            type: String,
            default: "stack",
            validator: (v) => ["stack", "row"].includes(v),
        },
        sharpeningLabel: {
            type: String,
            default: "Заказать заточку",
        },
        repairLabel: {
            type: String,
            default: "Заказать ремонт",
        },
        withDeliveryHint: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        wrapClass() {
            return this.layout === "row"
                ? "flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center"
                : "flex flex-col gap-3";
        },
    },
};
</script>

<template>
    <div>
        <p
            v-if="withDeliveryHint"
            class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 text-center mb-4"
        >
            Курьер заберёт заказ у вас и вернёт обратно после работы.
        </p>
        <div :class="wrapClass">
            <router-link
                to="/sharpening#order"
                class="bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-8 py-4 sm:py-5 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl text-center"
            >
                {{ sharpeningLabel }}
            </router-link>
            <router-link
                to="/repair#order"
                class="bg-dark-blue-500 hover:bg-dark-blue-600 dark:bg-dark-blue-600 dark:hover:bg-dark-blue-700 text-white px-8 py-4 sm:py-5 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl text-center"
            >
                {{ repairLabel }}
            </router-link>
        </div>
    </div>
</template>
