<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { equipmentService } from "../../services/EquipmentService.js";

export default {
    name: "EquipmentListPage",
    components: { ManagerLayout, PageHeader, DataTable },
    data() {
        return {
            loading: true,
            rows: [],
            query: "",
            columns: [
                { key: "id", label: "№" },
                { key: "title", label: "Название" },
                { key: "brand", label: "Бренд" },
                { key: "modelName", label: "Модель" },
                { key: "equipmentType", label: "Тип" },
            ],
        };
    },
    async mounted() { await this.load(); },
    methods: {
        async load() {
            this.loading = true;
            try {
                const data = await equipmentService.list({ query: this.query || undefined });
                this.rows = data.items || [];
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Оборудование</template>
        <PageHeader title="Оборудование" action-label="Добавить" :action-to="{ name: 'manager.equipment.create' }" />
        <div class="mb-4 flex gap-3">
            <input v-model="query" type="search" class="border border-slate-300 px-3 py-2 text-sm w-72" placeholder="Поиск..." @keyup.enter="load" />
            <button type="button" class="px-4 py-2 bg-dark-blue-500 text-white text-sm font-jost-bold" @click="load">Найти</button>
        </div>
        <DataTable :columns="columns" :rows="rows" :loading="loading">
            <template #actions="{ row }">
                <router-link :to="{ name: 'manager.equipment.view', params: { id: row.id } }" class="text-pink-500">👁</router-link>
            </template>
        </DataTable>
    </ManagerLayout>
</template>
