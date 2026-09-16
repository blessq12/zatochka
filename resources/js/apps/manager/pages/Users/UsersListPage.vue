<script>
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { staffUserService } from "../../services/StaffUserService.js";

const roleLabel = { manager: "Менеджер", master: "Мастер" };

export default {
    name: "UsersListPage",
    components: { PageHeader, DataTable },
    data() {
        return {
            loading: true,
            rows: [],
            search: "",
            columns: [
                { key: "id", label: "№" },
                { key: "name", label: "Имя" },
                { key: "email", label: "Эл. почта" },
                { key: "role", label: "Роль" },
            ],
        };
    },
    async mounted() {
        await this.load();
    },
    methods: {
        roleLabel,
        async load() {
            this.loading = true;
            try {
                const data = await staffUserService.list({ search: this.search || undefined });
                this.rows = data.items || [];
            } finally {
                this.loading = false;
            }
        },
        async remove(row) {
            if (!confirm(`Удалить сотрудника ${row.name}?`)) return;
            await staffUserService.remove(row.id);
            await this.load();
        },
    },
};
</script>

<template>
    <PageHeader title="Сотрудники" action-label="Добавить" :action-to="{ name: 'manager.users.create' }" />
        <div class="mb-4 flex gap-3">
            <input
                v-model="search"
                type="search"
                placeholder="Поиск..."
                class="border border-slate-300 px-3 py-2 text-sm w-72"
                @keyup.enter="load"
            />
            <button type="button" class="px-4 py-2 bg-dark-blue-500 text-white text-sm font-jost-bold" @click="load">
                Найти
            </button>
        </div>
        <DataTable :columns="columns" :rows="rows" :loading="loading">
            <template #cell-role="{ row }">{{ roleLabel(row.role) }}</template>
            <template #actions="{ row }">
                <router-link :to="{ name: 'manager.users.edit', params: { id: row.id } }" class="text-pink-500" title="Редактировать">✎</router-link>
                <button type="button" class="text-red-500" title="Удалить" @click="remove(row)">✕</button>
            </template>
        </DataTable>
</template>
