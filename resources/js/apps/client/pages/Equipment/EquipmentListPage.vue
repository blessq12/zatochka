<script>
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "ClientEquipmentListPage",
    data() {
        return {
            items: [],
            loading: false,
            error: null,
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await equipmentService.list();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось загрузить оборудование";
            } finally {
                this.loading = false;
            }
        },
        open(item) {
            this.$router.push({
                name: "client.equipment.show",
                params: { id: item.id },
            });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Оборудование</h1>
        </div>

        <p v-if="loading" class="text-base text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>
        <p
            v-else-if="!loading && items.length === 0"
            class="text-base text-slate-500"
        >
            Записей пока нет. Оборудование добавляет менеджер при приёме.
        </p>

        <div v-else class="space-y-3">
            <button
                v-for="item in items"
                :key="item.id"
                type="button"
                class="app-card w-full text-left"
                @click="open(item)"
            >
                <p class="font-jost-bold text-dark-blue-500">{{ item.name }}</p>
                <p class="mt-1 text-base text-slate-600">
                    {{ item.brand }} · {{ item.type }}
                </p>
                <p class="mt-1 text-base text-slate-500">
                    Модулей: {{ (item.modules || []).length }}
                </p>
            </button>
        </div>
    </div>
</template>
