<script>
export default {
    name: "ClientSectionCard",
    props: {
        title: {
            type: String,
            default: "",
        },
    },
};
</script>

<template>
    <div
        class="relative border border-dark-blue-500/30 bg-white/80 px-4 pb-5 pt-10 backdrop-blur-xl sm:px-6 sm:pb-6 sm:pt-11 lg:px-10 lg:pb-8 lg:pt-12 dark:border-dark-gray-200/90 dark:bg-dark-blue-500"
    >
        <h2
            v-if="title"
            class="absolute top-0 left-1/2 max-w-[90%] -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-center text-lg font-jost-bold text-[#C20A6C] sm:px-6 sm:text-xl dark:bg-dark-blue-500"
        >
            {{ title }}
        </h2>
        <div class="mt-2">
            <slot />
        </div>
    </div>
</template>
