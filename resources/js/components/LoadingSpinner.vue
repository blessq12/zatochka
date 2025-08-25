<template>
    <div v-if="loading" class="loading-spinner" :class="sizeClass">
        <div class="spinner-container">
            <div class="spinner"></div>
            <p v-if="text" class="loading-text">{{ text }}</p>
        </div>
    </div>
</template>

<script>
export default {
    name: "LoadingSpinner",
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
        text: {
            type: String,
            default: "Загрузка...",
        },
        size: {
            type: String,
            default: "medium",
            validator: (value) => ["small", "medium", "large"].includes(value),
        },
    },
    computed: {
        sizeClass() {
            return `spinner-${this.size}`;
        },
    },
};
</script>

<style scoped>
.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
}

.spinner-container {
    text-align: center;
}

.spinner {
    border: 4px solid rgb(229 231 235);
    border-top: 4px solid var(--accent-color, #3b82f6);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

.loading-text {
    margin-top: 1rem;
    color: rgb(75 85 99);
    font-size: 0.875rem;
}

.dark .loading-text {
    color: rgb(156 163 175);
}

.spinner-small .spinner {
    width: 1.5rem;
    height: 1.5rem;
}

.spinner-medium .spinner {
    width: 2rem;
    height: 2rem;
}

.spinner-large .spinner {
    width: 3rem;
    height: 3rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
