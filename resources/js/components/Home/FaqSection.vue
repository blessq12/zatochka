<script>
export default {
    name: "FaqSection",
    props: {
        items: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            openIndex: 0,
        };
    },
    methods: {
        toggle(index) {
            this.openIndex = this.openIndex === index ? -1 : index;
        },
    },
};
</script>

<template>
    <section
        class="bg-white/80 backdrop-blur-xl text-dark-gray-500 dark:bg-dark-blue-500/90 dark:backdrop-blur-xl dark:text-gray-100"
    >
        <div
            class="max-w-5xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 pb-12 sm:pb-16 lg:pb-20"
        >
            <h2
                class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] text-center mb-8 sm:mb-10"
            >
                ЧАСТЫЕ ВОПРОСЫ
            </h2>

            <div class="space-y-4">
                <div
                    v-for="(item, index) in items"
                    :key="item.id || index"
                    class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 overflow-hidden bg-white/80 backdrop-blur-xl dark:bg-transparent"
                >
                    <button
                        type="button"
                        class="w-full flex items-center justify-between px-4 sm:px-6 py-4 text-left hover:bg-white/90 dark:hover:bg-gray-900/20 transition-colors duration-200"
                        @click="toggle(index)"
                    >
                        <span
                            class="text-base sm:text-lg font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                        >
                            {{ item.question }}
                        </span>
                        <span
                            class="w-8 h-8 flex items-center justify-center border border-pink-500 dark:border-pink-600 text-pink-500 dark:text-pink-300 text-xl font-jost-bold"
                        >
                            {{ openIndex === index ? "−" : "+" }}
                        </span>
                    </button>
                    <transition name="faq">
                        <div
                            v-if="openIndex === index"
                            class="px-4 sm:px-6 pb-4 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200"
                        >
                            <p
                                v-for="(line, i) in item.answerLines"
                                :key="i"
                                class="mb-2"
                            >
                                {{ line }}
                            </p>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.faq-enter-active,
.faq-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.faq-enter-from,
.faq-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
