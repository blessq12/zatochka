<script>
export default {
    inject: ["themeTogglerService"],
    data() {
        return {};
    },
};
</script>

<template>
    <button @click="themeTogglerService.toggleTheme()">Toggle Theme</button>
</template>

<style scoped></style>
