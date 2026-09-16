<script>
export default {
    name: "PageHero",
    props: {
        title: {
            type: String,
            required: true,
        },
    },
};
</script>

<template>
    <section
        class="relative overflow-hidden bg-white dark:bg-dark-blue-500 pt-12 sm:pt-16 lg:pt-0 pb-16 sm:pb-20 lg:pb-24"
    >
        <!-- Закругленный низ секции для мобильных -->
        <div
            class="absolute w-200 h-200 sm:w-200 sm:h-200 lg:hidden bg-dark-blue-500 dark:bg-white bottom-0 left-1/2 -translate-x-1/2 rounded-full"
        ></div>

        <!-- Большой круг для десктопа -->
        <div
            class="hidden lg:block absolute -top-[30%] left-1/2 -translate-x-1/2 -translate-y-[30%] w-[800px] h-[800px] max-w-[800px] max-h-[800px] bg-dark-blue-500 dark:bg-white rounded-full"
        ></div>

        <div
            class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 relative z-10"
        >
            <!-- Контент внутри полукруга на десктопе -->
            <div
                class="lg:pt-12 xl:pt-16 lg:flex lg:flex-col lg:items-center lg:justify-center lg:h-[400px] lg:max-h-[400px]"
            >
                <h1
                    class="text-4xl sm:text-5xl lg:text-6xl font-jost-bold dark:text-dark-blue-500 text-white lg:text-white dark:lg:text-dark-blue-500 text-center mb-8 sm:mb-12 lg:max-w-[600px] xl:max-w-[700px] mx-auto px-4"
                >
                    {{ title }}
                </h1>

                <!-- Слот для декоративных элементов -->
                <slot></slot>
            </div>
        </div>
    </section>
</template>

<style scoped></style>
