<script>
import {
    BILLING_LABELS,
    KIND_LABELS,
    orderService,
    statusLabel,
    URGENCY_LABELS,
} from "../../services/OrderService.js";
import {
    draftStatusLabel,
    orderDraftService,
} from "../../services/OrderDraftService.js";

export default {
    name: "ClientOrderListPage",
    data() {
        return {
            scope: "drafts",
            orders: [],
            drafts: [],
            loading: false,
            error: null,
            statusLabel,
            draftStatusLabel,
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
                if (this.scope === "drafts") {
                    this.drafts = await orderDraftService.list();
                    this.orders = [];
                } else {
                    this.orders = await orderService.list(this.scope);
                    this.drafts = [];
                }
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить список";
            } finally {
                this.loading = false;
            }
        },
        openOrder(order) {
            this.$router.push({
                name: "client.orders.show",
                params: { id: order.id },
            });
        },
        openDraft(draft) {
            if (draft.status === "promoted" && draft.order_id) {
                this.$router.push({
                    name: "client.orders.show",
                    params: { id: draft.order_id },
                });
                return;
            }
            this.$router.push({
                name: "client.drafts.show",
                params: { id: draft.id },
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
        draftSummary(draft) {
            const items = draft.payload?.items || [];
            if (items.length === 0) {
                return draft.service_type === "repair" ? "Ремонт" : "Заточка";
            }
            return this.itemsSummary({ items });
        },
    },
};
</script>

<template>
    <div>
        <div class="mb-3 flex flex-row gap-1 bg-white/60 p-1 dark:bg-gray-800/60">
            <button
                type="button"
                class="flex-1 px-2 py-2.5 text-sm font-jost-bold sm:text-base"
                :class="
                    scope === 'drafts'
                        ? 'bg-[#C20A6C] text-white'
                        : 'text-dark-gray-500 dark:text-gray-200'
                "
                @click="scope = 'drafts'"
            >
                Заявки
            </button>
            <button
                type="button"
                class="flex-1 px-2 py-2.5 text-sm font-jost-bold sm:text-base"
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
                class="flex-1 px-2 py-2.5 text-sm font-jost-bold sm:text-base"
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

        <template v-else-if="scope === 'drafts'">
            <p
                v-if="!loading && drafts.length === 0"
                class="text-base text-dark-gray-500 dark:text-gray-300"
            >
                Заявок пока нет
            </p>
            <div v-else class="divide-y divide-white/10">
                <button
                    v-for="draft in drafts"
                    :key="draft.id"
                    type="button"
                    class="w-full py-3 text-left first:pt-0 last:pb-0"
                    @click="openDraft(draft)"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p
                                class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                            >
                                Заявка #{{ draft.id }}
                            </p>
                            <p
                                class="mt-1 truncate text-base text-dark-gray-500 dark:text-gray-300"
                            >
                                {{ draftSummary(draft) }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                        >
                            {{ draftStatusLabel(draft.status) }}
                        </span>
                    </div>
                </button>
            </div>
        </template>

        <template v-else>
            <p
                v-if="!loading && orders.length === 0"
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
                    @click="openOrder(order)"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p
                                class="font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                            >
                                Заказ #{{ order.id }}
                            </p>
                            <p
                                class="mt-1 truncate text-base text-dark-gray-500 dark:text-gray-300"
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
        </template>
    </div>
</template>
