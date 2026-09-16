<script>
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { clientService } from "../../services/ClientService.js";

export default {
    name: "ClientsListPage",
    components: { PageHeader, DataTable },
    data() {
        return {
            loading: true,
            rows: [],
            search: "",
            columns: [
                { key: "id", label: "№" },
                { key: "client", label: "Клиент" },
                { key: "email", label: "Email" },
                { key: "bonusBalance", label: "Бонусы" },
            ],
        };
    },
    async mounted() { await this.load(); },
    methods: {
        async load() {
            this.loading = true;
            try {
                const data = await clientService.list({ search: this.search || undefined });
                this.rows = data.items || [];
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <PageHeader title="Клиенты" action-label="Добавить" :action-to="{ name: 'manager.clients.create' }" />
        <div class="mb-4 flex gap-3">
            <input v-model="search" type="search" placeholder="ФИО / телефон..." class="border border-slate-300 px-3 py-2 text-sm w-72" @keyup.enter="load" />
            <button type="button" class="px-4 py-2 bg-dark-blue-500 text-white text-sm font-jost-bold" @click="load">Найти</button>
        </div>
        <DataTable :columns="columns" :rows="rows" :loading="loading">
            <template #cell-client="{ row }">
                <div class="font-jost-medium">{{ row.name || "—" }}</div>
                <div class="text-slate-500 text-xs">{{ row.phone }}</div>
            </template>
            <template #actions="{ row }">
                <router-link :to="{ name: 'manager.clients.view', params: { id: row.id } }" class="text-pink-500" title="Открыть">👁</router-link>
                <router-link :to="{ name: 'manager.clients.edit', params: { id: row.id } }" class="text-pink-500" title="Редактировать">✎</router-link>
            </template>
        </DataTable>
</template>
