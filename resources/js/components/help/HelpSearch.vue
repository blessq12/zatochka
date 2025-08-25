<template>
    <div class="mb-12" ref="searchSection">
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <input
                    type="text"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    placeholder="Поиск по вопросам..."
                    class="w-full px-6 py-4 pl-14 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/20 focus:outline-none transition-all duration-300"
                />
                <i
                    class="mdi mdi-magnify absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"
                ></i>
            </div>
            <div
                v-if="modelValue && resultsCount !== null"
                class="mt-4 text-center"
            >
                <span class="text-gray-600 dark:text-gray-400">
                    Найдено: {{ resultsCount }} вопросов
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "HelpSearch",
    props: {
        modelValue: {
            type: String,
            default: "",
        },
        resultsCount: {
            type: Number,
            default: null,
        },
    },
    emits: ["update:modelValue"],
};
</script>
