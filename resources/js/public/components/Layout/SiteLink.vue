<script>
export default {
    name: "SiteLink",
    props: {
        to: {
            type: String,
            required: true,
        },
        activeClass: {
            type: String,
            default: "",
        },
    },
    computed: {
        isActive() {
            const path = window.location.pathname.replace(/\/$/, "") || "/";
            const target = this.to.replace(/\/$/, "") || "/";
            return path === target;
        },
    },
};
</script>

<template>
    <a :href="to" :class="isActive ? activeClass : undefined">
        <slot />
    </a>
</template>
