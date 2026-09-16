<script>
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { orderService } from "../../services/OrderService.js";

export default {
    name: "OrdersListPage",
    components: { PageHeader, DataTable },
    data() {
        return {
            loading: true,
            rows: [],
            search: "",
            status: "",
            columns: [
                { key: "number", label: "Номер" },
                { key: "client", label: "Клиент" },
                { key: "status", label: "Статус" },
                { key: "serviceType", label: "Услуга" },
                { key: "estimatedAmount", label: "Сумма" },
            ],
        };
    },
    async mounted() { await this.load(); },
    methods: {
        async load() {
            this.loading = true;
            try {
                const data = await orderService.list({
                    search: this.search || undefined,
                    status: this.status || undefined,
                });
                this.rows = data.items || [];
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <PageHeader title="Заказы" action-label="Новый заказ" :action-to="{ name: 'manager.orders.create' }" />
        <div class="mb-4 flex flex-wrap gap-3">
            <input v-model="search" type="search" placeholder="Номер / клиент..." class="border border-slate-300 px-3 py-2 text-sm w-72" @keyup.enter="load" />
            <select v-model="status" class="border border-slate-300 px-3 py-2 text-sm" @change="load">
                <option value="">Все статусы</option>
                <option value="draft">draft</option>
                <option value="received">received</option>
                <option value="in_production">in_production</option>
                <option value="ready">ready</option>
                <option value="issued">issued</option>
                <option value="closed">closed</option>
                <option value="cancelled">cancelled</option>
            </select>
            <button type="button" class="px-4 py-2 bg-dark-blue-500 text-white text-sm font-jost-bold" @click="load">Найти</button>
        </div>
        <DataTable :columns="columns" :rows="rows" :loading="loading">
            <template #cell-client="{ row }">
                <div class="font-jost-medium">{{ row.client?.name || "—" }}</div>
                <div class="text-xs text-slate-500">{{ row.client?.phone }}</div>
            </template>
            <template #cell-estimatedAmount="{ row }">
                {{ row.estimatedAmount }} {{ row.estimatedCurrency }}
            </template>
            <template #actions="{ row }">
                <router-link :to="{ name: 'manager.orders.view', params: { id: row.id } }" class="text-pink-500" title="Открыть">👁</router-link>
            </template>
        </DataTable>
</template>
