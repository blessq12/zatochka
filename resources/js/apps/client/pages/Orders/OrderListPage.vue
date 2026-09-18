<script>
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import {
    BILLING_LABELS,
    KIND_LABELS,
    orderService,
    statusLabel,
    URGENCY_LABELS,
} from "../../services/OrderService.js";

export default {
    name: "ClientOrderListPage",
    components: { ClientSectionCard },
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
    <div class="space-y-3 lg:space-y-6">
        <ClientSectionCard title="ЗАКАЗЫ">
            <div
                class="mb-3 flex flex-row gap-1 border border-white/20 bg-white/60 p-1 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60 lg:mb-6 lg:gap-4 lg:p-2"
            >
                <button
                    type="button"
                    class="flex-1 px-3 py-2 text-sm font-jost-bold transition-all duration-300 lg:px-4 lg:py-3 lg:text-base"
                    :class="
                        scope === 'active'
                            ? 'bg-[#C3006B] text-white shadow-lg'
                            : 'text-dark-gray-500 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80'
                    "
                    @click="scope = 'active'"
                >
                    Активные
                </button>
                <button
                    type="button"
                    class="flex-1 px-3 py-2 text-sm font-jost-bold transition-all duration-300 lg:px-4 lg:py-3 lg:text-base"
                    :class="
                        scope === 'archive'
                            ? 'bg-[#C3006B] text-white shadow-lg'
                            : 'text-dark-gray-500 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80'
                    "
                    @click="scope = 'archive'"
                >
                    Архив
                </button>
            </div>

            <p v-if="loading" class="text-sm text-dark-gray-500 dark:text-gray-300 lg:text-base">
                Загрузка…
            </p>
            <p v-if="error" class="text-sm text-red-600 lg:text-base">{{ error }}</p>

            <p
                v-else-if="!loading && orders.length === 0"
                class="text-sm text-dark-gray-500 dark:text-gray-300 lg:text-base"
            >
                {{
                    scope === "active"
                        ? "Активных заказов нет"
                        : "В архиве пока пусто"
                }}
            </p>

            <div v-else class="divide-y divide-dark-blue-500/10 dark:divide-white/10 lg:space-y-4 lg:divide-y-0">
                <button
                    v-for="order in orders"
                    :key="order.id"
                    type="button"
                    class="w-full py-3 text-left transition first:pt-0 last:pb-0 hover:bg-white/40 lg:border lg:border-dark-blue-500/20 lg:bg-white/60 lg:p-4 lg:backdrop-blur-md lg:first:pt-4 lg:hover:border-[#C3006B]/40 dark:lg:border-gray-700/40 dark:lg:bg-gray-800/60"
                    @click="open(order)"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p
                                class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 lg:text-lg"
                            >
                                Заказ #{{ order.id }}
                            </p>
                            <p
                                class="mt-0.5 truncate text-sm text-dark-gray-500 dark:text-gray-300 lg:mt-1 lg:text-base"
                            >
                                {{ itemsSummary(order) }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-dark-gray-500 dark:text-gray-400 lg:mt-2 lg:text-sm">
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
        </ClientSectionCard>
    </div>
</template>
