<script>
import {
    BILLING_LABELS,
    KIND_LABELS,
    orderService,
    statusLabel,
    URGENCY_LABELS,
} from "../../services/OrderService.js";

export default {
    name: "ClientOrderListPage",
    data() {
        return {
            scope: "active",
            orders: [],
            loading: false,
            error: null,
            statusLabel,
            BILLING_LABELS,
            URGENCY_LABELS,
            KIND_LABELS,
        };
    },
    watch: {
        scope() {
            this.load();
        },
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.orders = await orderService.list(this.scope);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить заказы";
            } finally {
                this.loading = false;
            }
        },
        open(order) {
            this.$router.push({
                name: "client.orders.show",
                params: { id: order.id },
            });
        },
        itemsSummary(order) {
            const items = order.items || [];
            if (items.length === 0) return "—";
            return items
                .map((item) => {
                    if (item.kind === "sharpening") {
                        return `${item.title || "Заточка"} × ${item.quantity || 1}`;
                    }
                    return `Ремонт #${item.equipment_id || "—"}`;
                })
                .join(", ");
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Заказы</h1>
            <button
                type="button"
                class="app-btn-primary w-full sm:w-auto"
                @click="$router.push({ name: 'client.orders.create' })"
            >
                Новый заказ
            </button>
        </div>

        <div class="app-tabs">
            <button
                type="button"
                class="app-tab"
                :class="
                    scope === 'active'
                        ? 'border-pink-500 bg-pink-50 text-pink-700'
                        : 'border-slate-300 bg-white text-slate-700'
                "
                @click="scope = 'active'"
            >
                Активные
            </button>
            <button
                type="button"
                class="app-tab"
                :class="
                    scope === 'archive'
                        ? 'border-pink-500 bg-pink-50 text-pink-700'
                        : 'border-slate-300 bg-white text-slate-700'
                "
                @click="scope = 'archive'"
            >
                Архив
            </button>
        </div>

        <p v-if="loading" class="text-base text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <p
            v-else-if="!loading && orders.length === 0"
            class="text-base text-slate-500"
        >
            {{
                scope === "active"
                    ? "Активных заказов нет"
                    : "В архиве пока пусто"
            }}
        </p>

        <div v-else class="space-y-3">
            <button
                v-for="order in orders"
                :key="order.id"
                type="button"
                class="app-card w-full text-left"
                @click="open(order)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-jost-bold text-dark-blue-500">
                            Заказ #{{ order.id }}
                        </p>
                        <p class="mt-1 text-base text-slate-600">
                            {{ itemsSummary(order) }}
                        </p>
                    </div>
                    <span class="shrink-0 text-base text-slate-700">
                        {{ statusLabel(order.status) }}
                    </span>
                </div>
                <p class="mt-2 text-base text-slate-500">
                    {{ BILLING_LABELS[order.billing_type] || order.billing_type }}
                    ·
                    {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                    · {{ order.estimated_cost }} ₽
                </p>
            </button>
        </div>
    </div>
</template>
