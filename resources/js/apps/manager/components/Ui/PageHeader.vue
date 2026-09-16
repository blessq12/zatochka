<script>
export default {
    name: "PageHeader",
    props: {
        title: { type: String, required: true },
        actionLabel: { type: String, default: "" },
        actionTo: { type: [String, Object], default: null },
    },
};
</script>

<template>
    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">{{ title }}</h1>
        <router-link
            v-if="actionLabel && actionTo"
            :to="actionTo"
            class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 font-jost-bold text-sm"
        >
            {{ actionLabel }}
        </router-link>
        <slot />
    </div>
</template>
