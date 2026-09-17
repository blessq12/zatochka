<script>
import { warehouseService } from "../../services/WarehouseService.js";

export default {
    name: "WarehouseListPage",
    data() {
        return {
            items: [],
            category: "",
            loading: false,
            error: null,
            creating: false,
            form: {
                category: "spare_part",
                name: "",
                unit: "шт",
                qty_on_hand: "0",
            },
            receiveQty: {},
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
                    e.response?.data?.message || "Не удалось загрузить склад";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        async createItem() {
            this.creating = true;
            this.error = null;
            try {
                await warehouseService.create({
                    category: this.form.category,
                    name: this.form.name,
                    unit: this.form.unit,
                    qty_on_hand: this.form.qty_on_hand || "0",
                });
                this.form = {
                    category: "spare_part",
                    name: "",
                    unit: "шт",
                    qty_on_hand: "0",
                };
                await this.load();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось создать позицию";
            } finally {
                this.creating = false;
            }
        },
        async receive(item) {
            const qty = this.receiveQty[item.id];
            if (qty === "" || qty == null) {
                this.error = "Укажите количество прихода";
                return;
            }
            this.error = null;
            try {
                await warehouseService.receive(item.id, qty);
                this.receiveQty[item.id] = "";
                await this.load();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось оформить приход";
            }
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Склад</h1>
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

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>

        <section class="app-panel">
            <h2 class="text-lg font-jost-bold text-dark-blue-500">
                Новая позиция
            </h2>
            <div class="app-grid-2 lg:grid-cols-4">
                <select v-model="form.category" class="app-field">
                    <option value="spare_part">Запчасть</option>
                    <option value="consumable">Расходник</option>
                </select>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Название"
                    class="app-field"
                />
                <input
                    v-model="form.unit"
                    type="text"
                    placeholder="Ед. изм."
                    class="app-field"
                />
                <input
                    v-model="form.qty_on_hand"
                    type="number"
                    min="0"
                    step="0.001"
                    placeholder="Остаток"
                    class="app-field"
                />
            </div>
            <button
                type="button"
                class="app-btn-primary w-full sm:w-auto"
                :disabled="creating || !form.name || !form.unit"
                @click="createItem"
            >
                {{ creating ? "Создаю…" : "Добавить" }}
            </button>
        </section>

        <template v-if="!loading">
            <div class="app-card-list">
                <p v-if="items.length === 0" class="app-card text-slate-400">
                    Каталог пуст
                </p>
                <div v-for="item in items" :key="item.id" class="app-card">
                    <div class="flex items-start justify-between gap-2">
                        <div class="font-jost-medium text-dark-blue-500">
                            {{ item.name }}
                        </div>
                        <span class="text-xs text-slate-400">#{{ item.id }}</span>
                    </div>
                    <p class="text-sm text-slate-600">
                        {{ warehouseService.categoryLabel(item.category) }}
                    </p>
                    <p class="text-sm text-slate-700">
                        Остаток:
                        <span
                            :class="
                                Number(item.qty_on_hand) <= 0
                                    ? 'text-red-600'
                                    : 'text-dark-blue-500'
                            "
                        >
                            {{ item.qty_on_hand }}
                        </span>
                        {{ item.unit }}
                    </p>
                    <div class="app-actions">
                        <input
                            v-model="receiveQty[item.id]"
                            type="number"
                            min="0.001"
                            step="0.001"
                            class="app-field max-w-[8rem]"
                            placeholder="Кол-во"
                        />
                        <button
                            type="button"
                            class="app-btn-secondary"
                            @click="receive(item)"
                        >
                            Приход
                        </button>
                    </div>
                </div>
            </div>

            <div class="app-table-wrap">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">Название</th>
                            <th class="px-3 py-2">Категория</th>
                            <th class="px-3 py-2">Остаток</th>
                            <th class="px-3 py-2">Ед.</th>
                            <th class="px-3 py-2">Приход</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-t border-slate-100"
                        >
                            <td class="px-3 py-2 text-slate-400">{{ item.id }}</td>
                            <td class="px-3 py-2 font-jost-medium text-dark-blue-500">
                                {{ item.name }}
                            </td>
                            <td class="px-3 py-2">
                                {{ warehouseService.categoryLabel(item.category) }}
                            </td>
                            <td
                                class="px-3 py-2"
                                :class="
                                    Number(item.qty_on_hand) <= 0
                                        ? 'text-red-600'
                                        : ''
                                "
                            >
                                {{ item.qty_on_hand }}
                            </td>
                            <td class="px-3 py-2">{{ item.unit }}</td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <input
                                        v-model="receiveQty[item.id]"
                                        type="number"
                                        min="0.001"
                                        step="0.001"
                                        class="w-24 border border-slate-300 px-2 py-1"
                                        placeholder="Кол-во"
                                    />
                                    <button
                                        type="button"
                                        class="text-sm text-pink-600 hover:underline"
                                        @click="receive(item)"
                                    >
                                        Приход
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="px-3 py-6 text-center text-slate-400">
                                Каталог пуст
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
