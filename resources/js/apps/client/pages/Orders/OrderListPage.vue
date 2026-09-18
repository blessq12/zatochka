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
    <div>
        <div class="mb-3 flex flex-row gap-1 bg-white/60 p-1 dark:bg-gray-800/60">
            <button
                type="button"
                class="flex-1 px-3 py-2.5 text-base font-jost-bold"
                :class="
                    scope === 'active'
                        ? 'bg-[#C20A6C] text-white'
                        : 'text-dark-gray-500 dark:text-gray-200'
                "
                @click="scope = 'active'"
            >
                Активные
            </button>
            <button
                type="button"
                class="flex-1 px-3 py-2.5 text-base font-jost-bold"
                :class="
                    scope === 'archive'
                        ? 'bg-[#C20A6C] text-white'
                        : 'text-dark-gray-500 dark:text-gray-200'
                "
                @click="scope = 'archive'"
            >
                Архив
            </button>
        </div>

        <p v-if="loading" class="text-base text-dark-gray-500 dark:text-gray-300">
            Загрузка…
        </p>
        <p v-if="error" class="text-base text-red-600">{{ error }}</p>

        <p
            v-else-if="!loading && orders.length === 0"
            class="text-base text-dark-gray-500 dark:text-gray-300"
        >
            {{
                scope === "active"
                    ? "Активных заказов нет"
                    : "В архиве пока пусто"
            }}
        </p>

        <div v-else class="divide-y divide-white/10">
            <button
                v-for="order in orders"
                :key="order.id"
                type="button"
                class="w-full py-3 text-left first:pt-0 last:pb-0"
                @click="open(order)"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300">
                            Заказ #{{ order.id }}
                        </p>
                        <p class="mt-1 truncate text-base text-dark-gray-500 dark:text-gray-300">
                            {{ itemsSummary(order) }}
                        </p>
                    </div>
                    <span
                        class="shrink-0 text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                    >
                        {{ statusLabel(order.status) }}
                    </span>
                </div>
                <p class="mt-1 text-base text-dark-gray-500 dark:text-gray-400">
                    {{
                        BILLING_LABELS[order.billing_type] ||
                        order.billing_type
                    }}
                    ·
                    {{ URGENCY_LABELS[order.urgency] || order.urgency }}
                    · {{ order.estimated_cost }} ₽
                </p>
            </button>
        </div>
    </div>
</template>
