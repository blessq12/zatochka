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
        class="relative border border-dark-blue-500/30 bg-white/80 px-4 pb-4 pt-8 backdrop-blur-xl sm:px-6 sm:pb-5 sm:pt-9 lg:px-10 lg:pb-6 lg:pt-10 dark:border-dark-gray-200/90 dark:bg-dark-blue-500"
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
