<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { stockService } from "../../services/StockService.js";

export default {
    name: "StockListPage",
    components: { ManagerLayout, PageHeader, DataTable },
    data() {
        return {
            loading: true,
            rows: [],
            columns: [
                { key: "id", label: "№" },
                { key: "name", label: "Наименование" },
                { key: "sku", label: "Артикул" },
                { key: "quantity", label: "Остаток" },
                { key: "category", label: "Категория" },
            ],
        };
    },
    async mounted() { await this.load(); },
    methods: {
        async load() {
            this.loading = true;
            try {
                const data = await stockService.list();
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
        <template #title>Склад</template>
        <PageHeader title="Склад" />
        <DataTable :columns="columns" :rows="rows" :loading="loading">
            <template #actions="{ row }">
                <router-link :to="{ name: 'manager.stock.view', params: { id: row.id } }" class="text-pink-500">👁</router-link>
            </template>
        </DataTable>
    </ManagerLayout>
</template>
