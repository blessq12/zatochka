<script>
import PageHeader from "../../components/Ui/PageHeader.vue";
import DataTable from "../../components/Ui/DataTable.vue";
import { reviewService } from "../../services/ReviewService.js";

export default {
    name: "ReviewsListPage",
    components: { PageHeader, DataTable },
    data() {
        return {
            loading: true,
            status: "pending",
            rows: [],
            columns: [
                { key: "id", label: "№" },
                { key: "rating", label: "Оценка" },
                { key: "comment", label: "Комментарий" },
                { key: "status", label: "Статус" },
                { key: "submittedAt", label: "Дата" },
            ],
        };
    },
    async mounted() { await this.load(); },
    methods: {
        async load() {
            this.loading = true;
            try {
                const data = await reviewService.list({ status: this.status });
                this.rows = data.items || [];
            } finally {
                this.loading = false;
            }
        },
        async publish(row) {
            await reviewService.publish(row.id);
            await this.load();
        },
        async reject(row) {
            await reviewService.reject(row.id);
            await this.load();
        },
    },
};
</script>

<template>
    <PageHeader title="Отзывы" />
        <div class="mb-4">
            <select v-model="status" class="border border-slate-300 px-3 py-2 text-sm" @change="load">
                <option value="pending">На модерации</option>
                <option value="published">Опубликованные</option>
            </select>
        </div>
        <DataTable :columns="columns" :rows="rows" :loading="loading">
            <template #actions="{ row }">
                <router-link :to="{ name: 'manager.reviews.view', params: { id: row.id } }" class="text-pink-500">👁</router-link>
                <button type="button" class="text-green-600" title="Опубликовать" @click="publish(row)">✓</button>
                <button type="button" class="text-red-500" title="Отклонить" @click="reject(row)">✕</button>
            </template>
        </DataTable>
</template>
