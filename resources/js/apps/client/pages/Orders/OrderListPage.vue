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
    <div class="space-y-5 lg:space-y-6">
        <ClientSectionCard title="ЗАКАЗЫ">
            <div
                class="mb-5 flex flex-row gap-2 border border-white/20 bg-white/60 p-2 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60 lg:mb-6 lg:gap-4"
            >
                <button
                    type="button"
                    class="flex-1 px-4 py-3 text-base font-jost-bold transition-all duration-300 lg:px-4 lg:py-3 lg:text-base"
                    :class="
                        scope === 'active'
                            ? 'bg-[#C20A6C] text-white shadow-lg'
                            : 'text-dark-gray-500 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80'
                    "
                    @click="scope = 'active'"
                >
                    Активные
                </button>
                <button
                    type="button"
                    class="flex-1 px-4 py-3 text-base font-jost-bold transition-all duration-300 lg:px-4 lg:py-3 lg:text-base"
                    :class="
                        scope === 'archive'
                            ? 'bg-[#C20A6C] text-white shadow-lg'
                            : 'text-dark-gray-500 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80'
                    "
                    @click="scope = 'archive'"
                >
                    Архив
                </button>
            </div>

            <p v-if="loading" class="text-base text-dark-gray-500 dark:text-gray-300 lg:text-base">
                Загрузка…
            </p>
            <p v-if="error" class="text-base text-red-600 lg:text-base">{{ error }}</p>

            <p
                v-else-if="!loading && orders.length === 0"
                class="text-base text-dark-gray-500 dark:text-gray-300 lg:text-base"
            >
                {{
                    scope === "active"
                        ? "Активных заказов нет"
                        : "В архиве пока пусто"
                }}
            </p>

            <div v-else class="space-y-3 sm:space-y-4">
                <button
                    v-for="order in orders"
                    :key="order.id"
                    type="button"
                    class="w-full border border-dark-blue-500/20 bg-white/60 p-3 text-left backdrop-blur-md transition hover:border-[#C20A6C]/40 dark:border-gray-700/40 dark:bg-gray-800/60 sm:p-4"
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
                                class="mt-1 truncate text-base text-dark-gray-500 dark:text-gray-300 lg:mt-1 lg:text-base"
                            >
                                {{ itemsSummary(order) }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-base text-dark-gray-500 dark:text-gray-400 lg:mt-2 lg:text-base">
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
