<script>
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";
import { orderDisplayLabel } from "../../../../shared/orderDisplayLabel.js";
import { actorService } from "../../services/ActorService.js";
import {
    BILLING_LABELS,
    compositionLabel,
    orderService,
    STATUS_ORDER,
    statusBorderStyle,
    statusDotStyle,
    statusLabel,
    URGENCY_LABELS,
} from "../../services/OrderService.js";

export default {
    name: "OrderListPage",
    data() {
        return {
            items: [],
            clients: [],
            masters: [],
            clientId: this.$route.query.client_id || "",
            status: this.$route.query.status || "",
            loading: false,
            error: null,
            statusLabel,
            statusBorderStyle,
            statusDotStyle,
            STATUS_ORDER,
            compositionLabel,
            formatOrderDate,
            orderDisplayLabel,
            BILLING_LABELS,
            URGENCY_LABELS,
        };
    },
    watch: {
        "$route.query": {
            deep: true,
            handler() {
                this.clientId = this.$route.query.client_id || "";
                this.status = this.$route.query.status || "";
                this.load();
            },
        },
    },
    async mounted() {
        await Promise.all([this.loadClients(), this.loadMasters()]);
        await this.load();
    },
    methods: {
        async loadClients() {
            try {
                this.clients = await actorService.list("clients");
            } catch {
                this.clients = [];
            }
        },
        async loadMasters() {
            try {
                this.masters = await actorService.list("masters");
            } catch {
                this.masters = [];
            }
        },
        applyFilters() {
            const query = {};
            if (this.clientId) query.client_id = String(this.clientId);
            if (this.status) query.status = this.status;
            this.$router.replace({ name: "manager.orders", query });
        },
        async load() {
            this.loading = true;
            this.error = null;
            try {
                this.items = await orderService.list({
                    clientId: this.clientId || null,
                    status: this.status || null,
                });
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить список";
                this.items = [];
            } finally {
                this.loading = false;
            }
        },
        findClient(id) {
            return this.clients.find((c) => Number(c.id) === Number(id)) || null;
        },
        clientName(id) {
            const client = this.findClient(id);
            return client?.name || client?.email || `#${id}`;
        },
        clientPhone(id) {
            const client = this.findClient(id);
            return client?.phone || null;
        },
        masterName(id) {
            if (id == null) {
                return "не назначен";
            }
            const master = this.masters.find(
                (m) => Number(m.id) === Number(id),
            );
            return master?.name || master?.email || `#${id}`;
        },
        actualCostLabel(order) {
            if (order?.actual_cost == null || order.actual_cost === "") {
                return "—";
            }
            return String(order.actual_cost);
        },
        deliveryLabel(order) {
            if (!order.needs_delivery) {
                return "Без доставки";
            }
            return order.delivery_address
                ? `Доставка: ${order.delivery_address}`
                : "Доставка";
        },
        goCreate() {
            this.$router.push({ name: "manager.orders.create" });
        },
        goShow(item) {
            this.$router.push({
                name: "manager.orders.show",
                params: { id: String(item.id) },
            });
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
                @click="goCreate"
            >
                Создать
            </button>
        </div>

        <div class="app-filters">
            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Клиент</span>
                <select
                    v-model="clientId"
                    class="app-field"
                    @change="applyFilters"
                >
                    <option value="">Все</option>
                    <option
                        v-for="client in clients"
                        :key="client.id"
                        :value="String(client.id)"
                    >
                        {{ client.name || client.email || `#${client.id}` }}
                    </option>
                </select>
            </label>
            <label class="block space-y-1">
                <span class="text-sm text-slate-600">Статус</span>
                <select
                    v-model="status"
                    class="app-field"
                    @change="applyFilters"
                >
                    <option value="">Все</option>
                    <option v-for="s in STATUS_ORDER" :key="s" :value="s">
                        {{ statusLabel(s) }}
                    </option>
                </select>
            </label>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="!loading">
            <div
                class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-2 border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700 shadow-sm"
                role="note"
                aria-label="Легенда статусов"
            >
                <span
                    v-for="s in STATUS_ORDER"
                    :key="s"
                    class="inline-flex items-center gap-1.5"
                >
                    <span
                        class="inline-block h-3 w-3 shrink-0 rounded-full"
                        :style="statusDotStyle(s)"
                        aria-hidden="true"
                    />
                    {{ statusLabel(s) }}
                </span>
            </div>

            <div class="app-card-list">
                <p v-if="items.length === 0" class="app-card text-slate-500">
                    Пока пусто
                </p>
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="app-card w-full text-left"
                    :style="statusBorderStyle(item.status)"
                    :title="statusLabel(item.status)"
                    :aria-label="`Заказ ${orderDisplayLabel(item)}, ${statusLabel(item.status)}`"
                    @click="goShow(item)"
                >
                    <span class="font-jost-medium text-dark-blue-500">
                        Заказ {{ orderDisplayLabel(item) }}
                    </span>
                    <p class="text-sm text-slate-700">
                        {{ clientName(item.client_id) }}
                    </p>
                    <p
                        v-if="clientPhone(item.client_id)"
                        class="text-xs text-slate-500"
                    >
                        {{ clientPhone(item.client_id) }}
                    </p>
                    <p class="text-sm text-slate-600">
                        {{ URGENCY_LABELS[item.urgency] || item.urgency }}
                        ·
                        {{
                            BILLING_LABELS[item.billing_type] ||
                            item.billing_type
                        }}
                    </p>
                    <p class="text-sm text-slate-600">
                        Мастер: {{ masterName(item.master_id) }}
                    </p>
                    <p class="text-xs text-slate-500">
                        <span class="block leading-tight">
                            Ориентир {{ item.estimated_cost }}
                        </span>
                        <span class="block leading-tight">
                            Факт {{ actualCostLabel(item) }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Создан {{ formatOrderDate(item.created_at) }} · выдан
                        {{ formatOrderDate(item.issued_at) }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ compositionLabel(item) }}
                        · {{ deliveryLabel(item) }}
                    </p>
                </button>
            </div>

            <div class="app-table-wrap">
                <table class="min-w-full text-left text-sm">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-slate-600"
                    >
                        <tr>
                            <th class="px-4 py-3 font-jost-medium">#</th>
                            <th class="px-4 py-3 font-jost-medium">Клиент</th>
                            <th class="px-4 py-3 font-jost-medium">
                                <div
                                    class="flex flex-col gap-0.5 leading-tight"
                                >
                                    <span>Создан</span>
                                    <span>Выдан</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-jost-medium">
                                <div
                                    class="flex flex-col gap-0.5 leading-tight"
                                >
                                    <span>Тип</span>
                                    <span>Скорость</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-jost-medium">
                                <div
                                    class="flex flex-col gap-0.5 leading-tight"
                                >
                                    <span>Ориентир (₽)</span>
                                    <span>Факт (₽)</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-jost-medium">Мастер</th>
                            <th class="px-4 py-3 font-jost-medium">Состав</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="items.length === 0">
                            <td colspan="7" class="px-4 py-6 text-slate-500">
                                Пока пусто
                            </td>
                        </tr>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="cursor-pointer border-t border-slate-100 hover:bg-slate-50"
                            :style="statusBorderStyle(item.status)"
                            :title="statusLabel(item.status)"
                            @click="goShow(item)"
                        >
                            <td class="px-4 py-3">
                                {{ orderDisplayLabel(item) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-0.5 leading-tight">
                                    <span>{{ clientName(item.client_id) }}</span>
                                    <span
                                        v-if="clientPhone(item.client_id)"
                                        class="text-slate-500"
                                    >
                                        {{ clientPhone(item.client_id) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex flex-col gap-0.5 leading-tight"
                                >
                                    <span>{{
                                        formatOrderDate(item.created_at)
                                    }}</span>
                                    <span class="text-slate-500">
                                        {{ formatOrderDate(item.issued_at) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex flex-col gap-0.5 leading-tight"
                                >
                                    <span>
                                        {{
                                            BILLING_LABELS[item.billing_type] ||
                                            item.billing_type
                                        }}
                                    </span>
                                    <span class="text-slate-500">
                                        {{
                                            URGENCY_LABELS[item.urgency] ||
                                            item.urgency
                                        }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex flex-col gap-0.5 leading-tight"
                                >
                                    <span>{{ item.estimated_cost }}</span>
                                    <span class="text-slate-500">
                                        {{ actualCostLabel(item) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                {{ masterName(item.master_id) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ compositionLabel(item) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
