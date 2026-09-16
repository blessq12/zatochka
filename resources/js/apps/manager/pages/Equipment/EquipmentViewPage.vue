<script>
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "EquipmentViewPage",
        data() { return { item: null }; },
    async mounted() {
        this.item = await equipmentService.get(this.$route.params.id);
    },
};
</script>

<template>
    <div v-if="item" class="bg-white border border-slate-200 p-6 max-w-3xl">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500 mb-4">{{ item.title || item.brand }}</h1>
            <pre class="text-xs bg-slate-50 p-4 overflow-auto">{{ item }}</pre>
            <router-link :to="{ name: 'manager.equipment' }" class="inline-block mt-4 text-sm text-pink-500">← К списку</router-link>
        </div>
</template>
