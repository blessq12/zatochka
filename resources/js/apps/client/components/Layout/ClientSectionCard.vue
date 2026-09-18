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
        class="relative border border-dark-blue-500/30 bg-white/80 px-6 pb-6 pt-10 backdrop-blur-xl dark:border-dark-gray-200/90 dark:bg-dark-blue-500 sm:px-10 sm:pb-8 sm:pt-12"
    >
        <h2
            v-if="title"
            class="absolute left-1/2 top-0 max-w-[90%] -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-center text-lg font-jost-bold text-[#C20A6C] dark:bg-dark-blue-500 sm:px-6 sm:text-xl"
        >
            {{ title }}
        </h2>
        <div class="mt-2">
            <slot />
        </div>
    </div>
</template>
