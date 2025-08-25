<template>
    <div class="lg:w-80 flex-shrink-0">
        <div class="sticky top-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                Разделы помощи
            </h2>
            <nav class="space-y-2">
                <a
                    v-for="category in categories"
                    :key="category.id"
                    :href="`#${category.id}`"
                    class="sidebar-nav-item"
                    :class="{
                        active: activeCategory === category.id,
                    }"
                    @click.prevent="$emit('category-change', category.id)"
                >
                    <i :class="`${category.icon} text-lg mr-3`"></i>
                    <div>
                        <div class="font-semibold">
                            {{ category.title }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ category.description }}
                        </div>
                    </div>
                </a>
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    name: "HelpSidebar",
    props: {
        categories: {
            type: Array,
            required: true,
        },
        activeCategory: {
            type: String,
            default: "",
        },
    },
    emits: ["category-change"],
};
</script>

<style scoped>
.sidebar-nav-item {
    display: block;
    padding: 1rem;
    border-radius: 0.5rem;
    color: rgb(55 65 81);
    transition: all 0.2s;
    cursor: pointer;
}

.dark .sidebar-nav-item {
    color: rgb(209 213 219);
}

.sidebar-nav-item:hover {
    background-color: rgb(243 244 246);
}

.dark .sidebar-nav-item:hover {
    background-color: rgb(31 41 55);
}

.sidebar-nav-item.active {
    background-color: rgba(59, 130, 246, 0.1);
    color: var(--accent-color, #3b82f6);
    border-left: 4px solid var(--accent-color, #3b82f6);
}
</style>
