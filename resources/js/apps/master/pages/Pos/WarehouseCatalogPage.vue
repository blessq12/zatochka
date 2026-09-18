<script>
import { warehouseService } from "../../services/WarehouseService.js";

export default {
    name: "WarehouseCatalogPage",
    data() {
        return {
            items: [],
            category: "",
            loading: false,
            error: null,
            warehouseService,
        };
    },
    async mounted() {
        await this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await warehouseService.list(this.category || null);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить каталог";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Каталог склада</h1>
            <select
                v-model="category"
                class="app-field w-full sm:max-w-xs"
                @change="load"
            >
                <option value="">Все категории</option>
                <option value="spare_part">Запчасти</option>
                <option value="consumable">Расходники</option>
            </select>
        </div>

        <p class="text-sm text-slate-500">Только просмотр</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="!loading">
            <div class="app-card-list">
                <p v-if="items.length === 0" class="app-card text-slate-400">
                    Каталог пуст
                </p>
                <div v-for="item in items" :key="item.id" class="app-card">
                    <div class="font-jost-medium text-dark-blue-500">
                        {{ item.name }}
                    </div>
                    <p class="text-sm text-slate-600">
                        {{ warehouseService.categoryLabel(item.category) }}
                    </p>
                    <p class="text-sm text-slate-500">
                        {{ item.qty_on_hand }} {{ item.unit }}
                    </p>
                </div>
            </div>

            <div class="app-table-wrap">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2">Название</th>
                            <th class="px-3 py-2">Категория</th>
                            <th class="px-3 py-2">Остаток</th>
                            <th class="px-3 py-2">Ед.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-t border-slate-100"
                        >
                            <td class="px-3 py-2 font-jost-medium text-dark-blue-500">
                                {{ item.name }}
                            </td>
                            <td class="px-3 py-2">
                                {{ warehouseService.categoryLabel(item.category) }}
                            </td>
                            <td class="px-3 py-2">{{ item.qty_on_hand }}</td>
                            <td class="px-3 py-2">{{ item.unit }}</td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="4" class="px-3 py-6 text-center text-slate-400">
                                Каталог пуст
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
