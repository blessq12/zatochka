<script>
import { formatOrderDate } from "../../../../shared/formatOrderDate.js";
import { actorService } from "../../services/ActorService.js";
import {
    BILLING_LABELS,
    compositionLabel,
    orderService,
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
            compositionLabel,
            formatOrderDate,
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
        clientName(id) {
            const client = this.clients.find(
                (c) => Number(c.id) === Number(id),
            );
            return client?.name || client?.email || `#${id}`;
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
                    <option
                        v-for="s in [
                            'created',
                            'master_assigned',
                            'in_progress',
                            'waiting_parts',
                            'approval',
                            'works_completed',
                            'ready',
                            'issued',
                            'cancelled',
                        ]"
                        :key="s"
                        :value="s"
                    >
                        {{ statusLabel(s) }}
                    </option>
                </select>
            </label>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <template v-if="!loading">
            <div class="app-card-list">
                <p v-if="items.length === 0" class="app-card text-slate-500">
                    Пока пусто
                </p>
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="app-card w-full text-left"
                    @click="goShow(item)"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-jost-medium text-dark-blue-500">
                            Заказ #{{ item.id }}
                        </span>
                        <span class="text-xs text-pink-600">Открыть</span>
                    </div>
                    <p class="text-sm text-slate-700">
                        {{ clientName(item.client_id) }}
                    </p>
                    <p class="text-sm text-slate-600">
                        {{ statusLabel(item.status) }}
                        ·
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
                            <th class="px-4 py-3 font-jost-medium">Статус</th>
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
                            <th class="px-4 py-3 font-jost-medium" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="items.length === 0">
                            <td colspan="9" class="px-4 py-6 text-slate-500">
                                Пока пусто
                            </td>
                        </tr>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-t border-slate-100"
                        >
                            <td class="px-4 py-3">{{ item.id }}</td>
                            <td class="px-4 py-3">
                                {{ clientName(item.client_id) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ statusLabel(item.status) }}
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
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    class="text-pink-600 hover:underline"
                                    @click="goShow(item)"
                                >
                                    Открыть
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
