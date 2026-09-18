<script>
/**
 * Contact CTAs under public order forms (island-friendly props).
 */
export default {
    name: "FormContactActions",
    props: {
        phoneTel: {
            type: String,
            default: "",
        },
        writeHref: {
            type: String,
            default: "",
        },
    },
};
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center mb-8">
        <a
            v-if="phoneTel"
            :href="`tel:${phoneTel}`"
            class="bg-white dark:bg-dark-blue-500 hover:bg-gray-100 dark:hover:bg-dark-blue-400 text-dark-blue-500 dark:text-white border-2 border-dark-blue-500 dark:border-dark-blue-400 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform text-center"
        >
            ПОЗВОНИТЬ
        </a>
        <a
            v-if="writeHref"
            :href="writeHref"
            target="_blank"
            rel="noopener"
            class="bg-white dark:bg-dark-blue-500 hover:bg-gray-100 dark:hover:bg-dark-blue-400 text-dark-blue-500 dark:text-white border-2 border-dark-blue-500 dark:border-dark-blue-400 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform text-center"
        >
            НАПИСАТЬ
        </a>
    </div>
</template>
