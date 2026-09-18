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
    <!-- Mobile: плоский контент; desktop (lg+): фирменная секция -->
    <div
        class="lg:relative lg:border lg:border-dark-blue-500/30 lg:bg-white/80 lg:px-10 lg:pb-8 lg:pt-12 lg:backdrop-blur-xl dark:lg:border-dark-gray-200/90 dark:lg:bg-dark-blue-500"
    >
        <h2
            v-if="title"
            class="hidden lg:absolute lg:left-1/2 lg:top-0 lg:block lg:max-w-[90%] lg:-translate-x-1/2 lg:-translate-y-1/2 lg:bg-white lg:px-6 lg:text-center lg:text-xl lg:font-jost-bold lg:text-[#C20A6C] dark:lg:bg-dark-blue-500"
        >
            {{ title }}
        </h2>
        <div class="lg:mt-2">
            <slot />
        </div>
    </div>
</template>
