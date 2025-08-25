<template>
    <div class="help-item">
        <div class="question-header" @click="toggleAnswer">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ question.title }}
                </h3>
            </div>
            <i
                :class="`mdi mdi-chevron-down text-xl text-gray-500 transition-transform duration-300 ${
                    isOpen ? 'rotate-180' : ''
                }`"
            ></i>
        </div>
        <div
            v-show="isOpen"
            class="question-answer"
            :class="{ 'answer-open': isOpen }"
        >
            <div class="prose prose-gray dark:prose-invert max-w-none">
                <div v-html="question.answer"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "HelpQuestion",
    props: {
        question: {
            type: Object,
            required: true,
        },
        isOpen: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["toggle"],
    methods: {
        toggleAnswer() {
            this.$emit("toggle", this.question.id);
        },
    },
};
</script>

<style scoped>
.help-item {
    background-color: white;
    border-radius: 0.5rem;
    border: 1px solid rgb(229 231 235);
    overflow: hidden;
}

.dark .help-item {
    background-color: rgb(31 41 55);
    border-color: rgb(55 65 81);
}

.question-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.question-header:hover {
    background-color: rgb(249 250 251);
}

.dark .question-header:hover {
    background-color: rgb(55 65 81);
}

.question-answer {
    padding: 0 1.5rem 1.5rem;
    border-top: 1px solid rgb(229 231 235);
}

.dark .question-answer {
    border-top-color: rgb(55 65 81);
}

.answer-open {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
