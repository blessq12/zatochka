<script>
import { KIND_LABELS, orderService } from "../../services/OrderService.js";
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
            loading: false,
            acceptingId: null,
            error: null,
            KIND_LABELS,
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
                } else {
                    this.jobItems = await workshopService.listMine();
                }
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить заказы";
                this.queueItems = [];
                this.jobItems = [];
            } finally {
                this.loading = false;
            }
        },
        itemSummary(order) {
            return (order.items || [])
                .map((item) => KIND_LABELS[item.kind] || item.kind)
                .join(", ");
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
    <div class="space-y-4">
        <h1 class="text-2xl font-jost-bold text-dark-blue-500">Заказы</h1>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                class="border px-3 py-1.5 text-sm font-jost-medium"
                :class="
                    tab === item.id
                        ? 'border-pink-500 bg-pink-50 text-pink-600'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-pink-300'
                "
                @click="selectTab(item.id)"
            >
                {{ item.label }}
            </button>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="!loading && tab === 'queue'">
            <div
                v-if="queueItems.length === 0"
                class="border border-slate-200 bg-white px-4 py-6 text-sm text-slate-500"
            >
                Очередь пуста.
            </div>

            <ul
                v-else
                class="divide-y divide-slate-100 border border-slate-200 bg-white"
            >
                <li
                    v-for="order in queueItems"
                    :key="order.id"
                    class="flex flex-wrap items-center justify-between gap-3 px-4 py-3"
                >
                    <div class="space-y-1 text-sm">
                        <div class="font-jost-medium text-dark-blue-500">
                            Заказ #{{ order.id }}
                        </div>
                        <div class="text-slate-600">{{ itemSummary(order) }}</div>
                    </div>
                    <button
                        type="button"
                        class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600 disabled:opacity-60"
                        :disabled="acceptingId === order.id"
                        @click="accept(order)"
                    >
                        {{ acceptingId === order.id ? "Принимаю…" : "Принять" }}
                    </button>
                </li>
            </ul>
        </template>

        <template v-if="!loading && tab === 'in_work'">
            <div
                v-if="jobItems.length === 0"
                class="border border-slate-200 bg-white px-4 py-6 text-sm text-slate-500"
            >
                Нет открытых заданий.
            </div>

            <ul
                v-else
                class="divide-y divide-slate-100 border border-slate-200 bg-white"
            >
                <li
                    v-for="job in jobItems"
                    :key="job.id"
                    class="flex flex-wrap items-center justify-between gap-3 px-4 py-3"
                >
                    <div class="text-sm font-jost-medium text-dark-blue-500">
                        Заказ #{{ job.order_id }} · Job #{{ job.id }}
                    </div>
                    <button
                        type="button"
                        class="border border-pink-500 px-3 py-1.5 text-sm text-pink-600 hover:bg-pink-50"
                        @click="openJob(job.id)"
                    >
                        Открыть
                    </button>
                </li>
            </ul>
        </template>
    </div>
</template>
