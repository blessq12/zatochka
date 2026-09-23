<script>
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";
import { equipmentService } from "../../services/EquipmentService.js";
import {
    BILLING_LABELS,
    KIND_LABELS,
    URGENCY_LABELS,
    orderService,
    statusLabel,
} from "../../services/OrderService.js";
import { workshopService } from "../../services/WorkshopService.js";

export default {
    name: "EquipmentListPage",
    data() {
        return {
            searchQuery: "",
            searchTimer: null,
            items: [],
            loading: false,
            error: null,
            selectedId: null,
            orders: [],
            ordersLoading: false,
            ordersError: null,
            /** @type {Record<number, {loading: boolean, works: {title: string, moduleLabel: string|null}[], problem: string|null, error: string|null}>} */
            detailsByOrder: {},
            statusLabel,
            formatOrderDate,
            KIND_LABELS,
            BILLING_LABELS,
            URGENCY_LABELS,
        };
    },
    computed: {
        selected() {
            return this.items.find((item) => item.id === this.selectedId) || null;
        },
    },
    async mounted() {
        await this.loadEquipment();
    },
    beforeUnmount() {
        if (this.searchTimer) {
            clearTimeout(this.searchTimer);
        }
    },
    methods: {
        onSearchInput() {
            if (this.searchTimer) {
                clearTimeout(this.searchTimer);
            }
            this.searchTimer = setTimeout(() => {
                this.loadEquipment();
            }, 300);
        },
        async loadEquipment() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await equipmentService.list({
                    q: this.searchQuery.trim() || null,
                });
                if (
                    this.selectedId
                    && !this.items.some((item) => item.id === this.selectedId)
                ) {
                    this.selectedId = null;
                    this.orders = [];
                    this.detailsByOrder = {};
                }
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить список";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        modulesLabel(item) {
            const modules = item.modules || [];
            if (modules.length === 0) {
                return "Модулей нет";
            }
            return modules
                .map((m) => `${m.name} (${m.serial_number})`)
                .join(", ");
        },
        moduleLabel(moduleId) {
            if (moduleId == null || moduleId === "") {
                return null;
            }
            const id = Number(moduleId);
            const modules = this.selected?.modules || [];
            const found = modules.find((m) => Number(m.id) === id);
            if (!found) {
                return `Модуль #${id}`;
            }
            return `${found.name} (${found.serial_number})`;
        },
        async selectEquipment(item) {
            this.selectedId = item.id;
            this.orders = [];
            this.detailsByOrder = {};
            this.ordersLoading = true;
            this.ordersError = null;
            try {
                const all = await orderService.list({ equipmentId: item.id });
                const repairOrders = all.filter((order) =>
                    (order.items || []).some(
                        (row) =>
                            row.kind === "repair"
                            && Number(row.equipment_id) === Number(item.id),
                    ),
                );
                this.orders = repairOrders;
                await Promise.all(
                    repairOrders.map((order) =>
                        this.loadDetailsForOrder(order, item.id),
                    ),
                );
            } catch (e) {
                this.ordersError =
                    e.response?.data?.message ||
                    "Не удалось загрузить заказы по оборудованию";
                this.orders = [];
            } finally {
                this.ordersLoading = false;
            }
        },
        async loadDetailsForOrder(order, equipmentId) {
            const orderId = order.id;
            const repairItems = (order.items || []).filter(
                (row) =>
                    row.kind === "repair"
                    && Number(row.equipment_id) === Number(equipmentId),
            );
            const problem =
                repairItems.map((row) => row.problem).filter(Boolean).join("; ")
                || null;

            this.detailsByOrder = {
                ...this.detailsByOrder,
                [orderId]: {
                    loading: true,
                    works: [],
                    problem,
                    error: null,
                },
            };
            try {
                let job = null;
                try {
                    job = await workshopService.getByOrder(orderId);
                } catch (e) {
                    if (e.response?.status !== 404) {
                        throw e;
                    }
                }
                const repairItemIds = repairItems.map((row) => Number(row.id));
                const works = [];
                for (const jobItem of job?.items || []) {
                    if (!repairItemIds.includes(Number(jobItem.order_item_id))) {
                        continue;
                    }
                    for (const work of jobItem.works || []) {
                        const title = (work.title || "").trim();
                        const moduleLabel = this.moduleLabel(
                            work.equipment_module_id,
                        );
                        if (!title && !moduleLabel) {
                            continue;
                        }
                        works.push({
                            title: title || "Без названия",
                            moduleLabel,
                        });
                    }
                }
                this.detailsByOrder = {
                    ...this.detailsByOrder,
                    [orderId]: {
                        loading: false,
                        works,
                        problem,
                        error: null,
                    },
                };
            } catch (e) {
                this.detailsByOrder = {
                    ...this.detailsByOrder,
                    [orderId]: {
                        loading: false,
                        works: [],
                        problem,
                        error:
                            e.response?.data?.message ||
                            "Не удалось загрузить работы",
                    },
                };
            }
        },
    },
};
</script>

<template>
    <div class="app-page">
        <h1 class="app-page-title">Оборудование</h1>

        <div
            class="grid gap-4 lg:grid-cols-[minmax(16rem,22rem)_minmax(0,1fr)] lg:items-start lg:gap-6"
        >
            <aside class="space-y-3 lg:sticky lg:top-4">
                <label class="block space-y-1">
                    <span class="text-sm text-slate-600">Поиск</span>
                    <input
                        v-model="searchQuery"
                        type="search"
                        class="app-field"
                        placeholder="Название, бренд, тип, серийник…"
                        @input="onSearchInput"
                    />
                </label>

                <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                <div
                    v-if="!loading"
                    class="max-h-[70vh] space-y-2 overflow-y-auto pr-1"
                >
                    <p
                        v-if="items.length === 0"
                        class="border border-slate-300 bg-white px-3 py-4 text-sm text-slate-500 shadow-sm"
                    >
                        Ничего не найдено
                    </p>
                    <button
                        v-for="item in items"
                        :key="item.id"
                        type="button"
                        class="w-full border border-slate-300 bg-white p-3 text-left shadow-sm transition"
                        :class="
                            selectedId === item.id
                                ? 'border-pink-500 bg-pink-50'
                                : 'hover:border-pink-300'
                        "
                        @click="selectEquipment(item)"
                    >
                        <div class="font-jost-medium text-dark-blue-500">
                            {{ item.name }}
                        </div>
                        <p class="text-sm text-slate-700">
                            {{ item.brand }} · {{ item.type }}
                        </p>
                        <p class="text-xs text-slate-500">
                            #{{ item.id }} · модулей
                            {{ item.modules?.length || 0 }}
                        </p>
                    </button>
                </div>
            </aside>

            <div class="min-w-0 space-y-4">
                <p
                    v-if="!selected"
                    class="border border-slate-300 bg-white px-3 py-6 text-sm text-slate-500 shadow-sm"
                >
                    Выберите оборудование слева
                </p>

                <template v-if="selected">
                    <section
                        class="space-y-3 border border-slate-300 bg-white p-3 shadow-sm sm:p-4"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <h2
                                class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                            >
                                {{ selected.name }}
                            </h2>
                            <span class="text-xs text-slate-500"
                                >#{{ selected.id }}</span
                            >
                        </div>
                        <dl class="grid gap-2 text-sm sm:grid-cols-2">
                            <div class="flex justify-between gap-2 sm:block">
                                <dt class="text-slate-500">Бренд</dt>
                                <dd class="text-slate-800">
                                    {{ selected.brand }}
                                </dd>
                            </div>
                            <div class="flex justify-between gap-2 sm:block">
                                <dt class="text-slate-500">Тип</dt>
                                <dd class="text-slate-800">
                                    {{ selected.type }}
                                </dd>
                            </div>
                        </dl>
                        <div class="border-t border-slate-100 pt-3">
                            <p class="text-sm font-jost-medium text-dark-blue-500">
                                Модули
                            </p>
                            <p
                                v-if="!(selected.modules || []).length"
                                class="mt-1 text-sm text-slate-500"
                            >
                                Модулей нет
                            </p>
                            <ul
                                v-else
                                class="mt-1 space-y-1 text-sm text-slate-700"
                            >
                                <li
                                    v-for="module in selected.modules"
                                    :key="module.id || module.serial_number"
                                >
                                    {{ module.name }}
                                    <span class="text-slate-500">
                                        · {{ module.serial_number }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <section class="space-y-3">
                        <h2
                            class="text-base font-jost-bold text-dark-blue-500 lg:text-lg"
                        >
                            Хронология ремонтов
                        </h2>

                        <p
                            v-if="ordersLoading"
                            class="text-sm text-slate-500"
                        >
                            Загрузка…
                        </p>
                        <p v-if="ordersError" class="text-sm text-red-600">
                            {{ ordersError }}
                        </p>

                        <template v-if="!ordersLoading">
                            <p
                                v-if="orders.length === 0"
                                class="border border-slate-300 bg-white px-3 py-4 text-sm text-slate-500 shadow-sm"
                            >
                                Ремонтов по этой технике пока нет
                            </p>
                            <div
                                v-for="order in orders"
                                :key="order.id"
                                class="space-y-2 border border-slate-300 bg-white p-3 shadow-sm"
                            >
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <span
                                        class="font-jost-medium text-dark-blue-500"
                                    >
                                        Заказ #{{ order.id }}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        {{ statusLabel(order.status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-700">
                                    {{
                                        URGENCY_LABELS[order.urgency] ||
                                        order.urgency
                                    }}
                                    ·
                                    {{
                                        BILLING_LABELS[order.billing_type] ||
                                        order.billing_type
                                    }}
                                </p>
                                <p class="text-sm text-slate-600">
                                    Создан
                                    {{ formatOrderDate(order.created_at) }}
                                    · выдан
                                    {{ formatOrderDate(order.issued_at) }}
                                </p>
                                <p
                                    v-if="detailsByOrder[order.id]?.problem"
                                    class="text-sm text-slate-600"
                                >
                                    Проблема:
                                    {{ detailsByOrder[order.id].problem }}
                                </p>
                                <div>
                                    <p class="text-xs text-slate-500">Работы</p>
                                    <p
                                        v-if="detailsByOrder[order.id]?.loading"
                                        class="text-sm text-slate-500"
                                    >
                                        …
                                    </p>
                                    <p
                                        v-else-if="
                                            detailsByOrder[order.id]?.error
                                        "
                                        class="text-sm text-red-600"
                                    >
                                        {{ detailsByOrder[order.id].error }}
                                    </p>
                                    <ul
                                        v-else-if="
                                            (
                                                detailsByOrder[order.id]
                                                    ?.works || []
                                            ).length
                                        "
                                        class="mt-1 list-disc space-y-0.5 pl-5 text-sm text-slate-700"
                                    >
                                        <li
                                            v-for="(work, idx) in detailsByOrder[
                                                order.id
                                            ].works"
                                            :key="idx"
                                        >
                                            {{ work.title }}
                                            <span
                                                v-if="work.moduleLabel"
                                                class="text-slate-500"
                                            >
                                                · {{ work.moduleLabel }}
                                            </span>
                                        </li>
                                    </ul>
                                    <p
                                        v-else
                                        class="text-sm text-slate-500"
                                    >
                                        Работы не указаны
                                    </p>
                                </div>
                            </div>
                        </template>
                    </section>
                </template>
            </div>
        </div>
    </div>
</template>
