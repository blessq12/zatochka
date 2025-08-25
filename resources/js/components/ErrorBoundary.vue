<template>
    <div v-if="error" class="error-boundary">
        <div class="max-w-md mx-auto text-center p-8">
            <div class="mb-6">
                <i class="mdi mdi-alert-circle text-6xl text-red-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Что-то пошло не так
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Произошла ошибка при загрузке компонента. Попробуйте обновить
                страницу.
            </p>
            <div class="space-y-3">
                <button
                    @click="retry"
                    class="w-full bg-accent hover:bg-accent/90 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
                >
                    Попробовать снова
                </button>
                <button
                    @click="goHome"
                    class="w-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
                >
                    На главную
                </button>
            </div>
            <div
                v-if="showDetails"
                class="mt-6 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg text-left"
            >
                <details class="text-sm text-gray-600 dark:text-gray-400">
                    <summary class="cursor-pointer font-semibold mb-2">
                        Детали ошибки
                    </summary>
                    <pre class="whitespace-pre-wrap">{{ error.message }}</pre>
                    <pre
                        v-if="error.stack"
                        class="whitespace-pre-wrap text-xs mt-2"
                        >{{ error.stack }}</pre
                    >
                </details>
            </div>
        </div>
    </div>
    <slot v-else></slot>
</template>

<script>
export default {
    name: "ErrorBoundary",
    props: {
        showDetails: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            error: null,
        };
    },
    methods: {
        handleError(error) {
            console.error("ErrorBoundary caught error:", error);
            this.error = error;
        },
        retry() {
            this.error = null;
            this.$emit("retry");
        },
        goHome() {
            window.location.href = "/";
        },
    },
    errorCaptured(error, instance, info) {
        this.handleError(error);
        return false; // Предотвращаем дальнейшее распространение ошибки
    },
};
</script>

<style scoped>
.error-boundary {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgb(249 250 251);
}

.dark .error-boundary {
    background-color: rgb(17 24 39);
}
</style>
