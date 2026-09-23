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

const TABS = [
    { id: "queue", label: "Очередь" },
    { id: "in_work", label: "В работе" },
];

export default {
    name: "OrdersPage",
    data() {
        return {
            tabs: TABS,
            tab: this.$route.query.tab === "in_work" ? "in_work" : "queue",
            queueItems: [],
            jobItems: [],
            /** @type {Record<number, object|null>} */
            ordersById: {},
            /** @type {Record<number, object>} */
            equipmentById: {},
            loading: false,
            acceptingId: null,
            error: null,
            KIND_LABELS,
            BILLING_LABELS,
            URGENCY_LABELS,
            statusLabel,
            formatOrderDate,
        };
    },
    watch: {
        "$route.query.tab"(value) {
            const next = value === "in_work" ? "in_work" : "queue";
            if (next !== this.tab) {
                this.tab = next;
                this.load();
            }
        },
    },
    async mounted() {
        await this.load();
    },
    methods: {
        selectTab(tab) {
            this.tab = tab;
            const query = { ...this.$route.query };
            if (tab === "in_work") {
                query.tab = "in_work";
            } else {
                delete query.tab;
            }
            this.$router.replace({ name: "pos.orders", query });
            this.load();
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                if (this.tab === "queue") {
                    this.queueItems = await orderService.listAssigned();
                    this.jobItems = [];
                    this.ordersById = {};
                    await this.hydrateEquipments(this.queueItems);
                } else {
                    this.jobItems = await workshopService.listMine();
                    this.queueItems = [];
                    await this.hydrateJobOrders(this.jobItems);
                    await this.hydrateEquipments(
                        Object.values(this.ordersById).filter(Boolean),
                    );
                }
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить заказы";
                this.queueItems = [];
                this.jobItems = [];
                this.ordersById = {};
                this.equipmentById = {};
            } finally {
                this.loading = false;
            }
        },
        async hydrateJobOrders(jobs) {
            const map = {};
            await Promise.all(
                jobs.map(async (job) => {
                    try {
                        map[job.order_id] = await orderService.get(job.order_id);
                    } catch {
                        map[job.order_id] = null;
                    }
                }),
            );
            this.ordersById = map;
        },
        async hydrateEquipments(orders) {
            const ids = [
                ...new Set(
                    (orders || [])
                        .flatMap((order) => order?.items || [])
                        .filter(
                            (item) =>
                                item.kind === "repair" && item.equipment_id,
                        )
                        .map((item) => Number(item.equipment_id)),
                ),
            ];
            const map = { ...this.equipmentById };
            await Promise.all(
                ids.map(async (id) => {
                    if (map[id]) {
                        return;
                    }
                    try {
                        map[id] = await equipmentService.get(id);
                    } catch {
                        map[id] = {
                            id,
                            name: null,
                            brand: null,
                            type: null,
                            modules: [],
                        };
                    }
                }),
            );
            this.equipmentById = map;
        },
        repairEquipments(order) {
            const ids = [
                ...new Set(
                    (order?.items || [])
                        .filter(
                            (item) =>
                                item.kind === "repair" && item.equipment_id,
                        )
                        .map((item) => Number(item.equipment_id)),
                ),
            ];
            return ids.map((id) => {
                const equipment = this.equipmentById[id] || null;
                const problems = (order?.items || [])
                    .filter(
                        (row) =>
                            row.kind === "repair"
                            && Number(row.equipment_id) === id
                            && row.problem,
                    )
                    .map((row) => row.problem);
                return { id, equipment, problems };
            });
        },
        equipmentTitle(entry) {
            return entry.equipment?.name || `Оборудование #${entry.id}`;
        },
        equipmentMeta(entry) {
            const parts = [entry.equipment?.brand, entry.equipment?.type].filter(
                Boolean,
            );
            return parts.length ? parts.join(" · ") : null;
        },
        modulesLabel(equipment) {
            const modules = equipment?.modules || [];
            if (modules.length === 0) {
                return "Модулей нет";
            }
            return modules
                .map((m) => `${m.name} (${m.serial_number})`)
                .join(", ");
        },
        itemsSummary(order) {
            const rows = order?.items || [];
            if (rows.length === 0) {
                return "Без позиций";
            }
            return rows
                .map((row) => {
                    if (row.kind === "sharpening") {
                        const title = row.title || "Заточка";
                        const qty =
                            row.quantity != null ? ` ×${row.quantity}` : "";
                        return `${title}${qty}`;
                    }
                    const problem = row.problem ? `: ${row.problem}` : "";
                    return row.equipment_id
                        ? `Ремонт #${row.equipment_id}${problem}`
                        : `Ремонт${problem}`;
                })
                .join("; ");
        },
        kindsSummary(order) {
            const kinds = [...new Set((order?.items || []).map((i) => i.kind))];
            return kinds.map((k) => KIND_LABELS[k] || k).join(", ") || "—";
        },
        jobWorksCount(job) {
            return (job.items || []).reduce(
                (sum, item) => sum + (item.works?.length || 0),
                0,
            );
        },
        orderForJob(job) {
            return this.ordersById[job.order_id] || null;
        },
        async accept(order) {
            this.acceptingId = order.id;
            this.error = null;
            try {
                const itemIds = (order.items || []).map((item) => item.id);
                const job = await workshopService.accept(order.id, itemIds);
                await this.$router.push({
                    name: "pos.job",
                    params: { id: String(job.id) },
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось принять заказ";
            } finally {
                this.acceptingId = null;
            }
        },
        openJob(id) {
            this.$router.push({ name: "pos.job", params: { id: String(id) } });
        },
    },
};
</script>

<template>
    <div class="app-page">
        <h1 class="app-page-title">Заказы</h1>

        <div class="app-tabs">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="app-tab"
                :class="
                    tab === item.id
                        ? 'border-pink-500 bg-pink-50 text-pink-700'
                        : 'border-slate-300 bg-white text-slate-700 hover:border-pink-400'
                "
                @click="selectTab(item.id)"
            >
                {{ item.label }}
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="!loading && tab === 'queue'">
            <p v-if="queueItems.length === 0" class="app-card text-sm text-slate-500">
                Очередь пуста
            </p>

            <ul v-else class="space-y-3">
                <li
                    v-for="order in queueItems"
                    :key="order.id"
                    class="app-card"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-jost-medium text-dark-blue-500">
                            Заказ #{{ order.id }}
                        </span>
                        <span class="text-xs text-slate-500">
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-700">
                        {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                        ·
                        {{ BILLING_LABELS[order.billing_type] || order.billing_type }}
                        · оценка {{ order.estimated_cost }} ₽
                    </p>
                    <p class="text-sm text-slate-600">
                        {{ itemsSummary(order) }}
                    </p>
                    <div
                        v-if="repairEquipments(order).length"
                        class="space-y-2 border-t border-slate-100 pt-2"
                    >
                        <div
                            v-for="entry in repairEquipments(order)"
                            :key="entry.id"
                            class="text-sm"
                        >
                            <p class="font-jost-medium text-dark-blue-500">
                                {{ equipmentTitle(entry) }}
                                <span class="text-xs font-normal text-slate-500">
                                    #{{ entry.id }}
                                </span>
                            </p>
                            <p
                                v-if="equipmentMeta(entry)"
                                class="text-slate-700"
                            >
                                {{ equipmentMeta(entry) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Модули: {{ modulesLabel(entry.equipment) }}
                            </p>
                            <p
                                v-if="entry.problems.length"
                                class="text-xs text-slate-600"
                            >
                                Проблема: {{ entry.problems.join("; ") }}
                            </p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">
                        {{ kindsSummary(order) }}
                        · позиций {{ (order.items || []).length }}
                        <template v-if="order.needs_delivery">
                            · доставка
                        </template>
                    </p>
                    <p class="text-xs text-slate-500">
                        Создан {{ formatOrderDate(order.created_at) }}
                        · выдан {{ formatOrderDate(order.issued_at) }}
                    </p>
                    <button
                        type="button"
                        class="app-btn-primary mt-1 w-full sm:w-auto"
                        :disabled="acceptingId === order.id"
                        @click="accept(order)"
                    >
                        {{ acceptingId === order.id ? "Принимаю…" : "Принять" }}
                    </button>
                </li>
            </ul>
        </template>

        <template v-if="!loading && tab === 'in_work'">
            <p v-if="jobItems.length === 0" class="app-card text-sm text-slate-500">
                Нет открытых заданий
            </p>

            <ul v-else class="space-y-3">
                <li
                    v-for="job in jobItems"
                    :key="job.id"
                    class="app-card"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-jost-medium text-dark-blue-500">
                            Заказ #{{ job.order_id }}
                        </span>
                        <span class="text-xs text-slate-500">
                            задание #{{ job.id }}
                        </span>
                    </div>
                    <template v-if="orderForJob(job)">
                        <p class="text-sm text-slate-700">
                            {{ statusLabel(orderForJob(job).status) }}
                            ·
                            {{
                                URGENCY_LABELS[orderForJob(job).urgency] ||
                                orderForJob(job).urgency
                            }}
                            ·
                            {{
                                BILLING_LABELS[orderForJob(job).billing_type] ||
                                orderForJob(job).billing_type
                            }}
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ itemsSummary(orderForJob(job)) }}
                        </p>
                        <div
                            v-if="repairEquipments(orderForJob(job)).length"
                            class="space-y-2 border-t border-slate-100 pt-2"
                        >
                            <div
                                v-for="entry in repairEquipments(
                                    orderForJob(job),
                                )"
                                :key="entry.id"
                                class="text-sm"
                            >
                                <p class="font-jost-medium text-dark-blue-500">
                                    {{ equipmentTitle(entry) }}
                                    <span
                                        class="text-xs font-normal text-slate-500"
                                    >
                                        #{{ entry.id }}
                                    </span>
                                </p>
                                <p
                                    v-if="equipmentMeta(entry)"
                                    class="text-slate-700"
                                >
                                    {{ equipmentMeta(entry) }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    Модули:
                                    {{ modulesLabel(entry.equipment) }}
                                </p>
                                <p
                                    v-if="entry.problems.length"
                                    class="text-xs text-slate-600"
                                >
                                    Проблема: {{ entry.problems.join("; ") }}
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">
                            Создан
                            {{ formatOrderDate(orderForJob(job).created_at) }}
                            · выдан
                            {{ formatOrderDate(orderForJob(job).issued_at) }}
                        </p>
                    </template>
                    <p class="text-xs text-slate-500">
                        Позиций в задании {{ (job.items || []).length }}
                        · работ записано {{ jobWorksCount(job) }}
                    </p>
                    <button
                        type="button"
                        class="app-btn-secondary mt-1 w-full sm:w-auto"
                        @click="openJob(job.id)"
                    >
                        Открыть
                    </button>
                </li>
            </ul>
        </template>
    </div>
</template>
