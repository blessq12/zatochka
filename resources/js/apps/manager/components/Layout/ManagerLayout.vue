<script>
import AppSidebar from "./AppSidebar.vue";
import AppTopbar from "./AppTopbar.vue";

export default {
    name: "ManagerLayout",
    components: { AppSidebar, AppTopbar },
};
</script>

<template>
    <div class="min-h-screen flex bg-slate-100 font-jost-regular">
        <AppSidebar />
        <div class="flex-1 flex flex-col min-w-0">
            <AppTopbar>
                <slot name="title">Менеджер</slot>
            </AppTopbar>
            <main class="flex-1 p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
