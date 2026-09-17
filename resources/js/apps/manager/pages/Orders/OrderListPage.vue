<script>
import {
    orderService,
    statusLabel,
    BILLING_LABELS,
    URGENCY_LABELS,
    KIND_LABELS,
} from "../../services/OrderService.js";
import { actorService } from "../../services/ActorService.js";

export default {
    name: "OrderListPage",
    data() {
        return {
            items: [],
            clients: [],
            clientId: this.$route.query.client_id || "",
            status: this.$route.query.status || "",
            loading: false,
            error: null,
            statusLabel,
            BILLING_LABELS,
            URGENCY_LABELS,
            KIND_LABELS,
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
        await this.loadClients();
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
            const client = this.clients.find((c) => c.id === id);
            return client?.name || client?.email || `#${id}`;
        },
        kindsSummary(order) {
            const kinds = [...new Set((order.items || []).map((i) => i.kind))];
            return kinds.map((k) => KIND_LABELS[k] || k).join(", ") || "—";
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
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-jost-bold text-dark-blue-500">Заказы</h1>
            <button
                type="button"
                class="bg-pink-500 px-4 py-2 text-sm font-jost-medium text-white hover:bg-pink-600"
                @click="goCreate"
            >
                Создать
            </button>
        </div>

        <div class="flex flex-wrap gap-3">
            <label class="block min-w-[12rem] flex-1 space-y-1">
                <span class="text-sm text-slate-600">Клиент</span>
                <select
                    v-model="clientId"
                    class="w-full border border-slate-300 px-3 py-2"
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
            <label class="block min-w-[12rem] flex-1 space-y-1">
                <span class="text-sm text-slate-600">Статус</span>
                <select
                    v-model="status"
                    class="w-full border border-slate-300 px-3 py-2"
                    @change="applyFilters"
                >
                    <option value="">Все</option>
                    <option
                        v-for="s in [
                            'created',
                            'master_assigned',
                            'in_progress',
                            'waiting_parts',
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

        <div v-if="!loading" class="overflow-x-auto border border-slate-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-jost-medium">#</th>
                        <th class="px-4 py-3 font-jost-medium">Клиент</th>
                        <th class="px-4 py-3 font-jost-medium">Статус</th>
                        <th class="px-4 py-3 font-jost-medium">Тип оплаты</th>
                        <th class="px-4 py-3 font-jost-medium">Срочность</th>
                        <th class="px-4 py-3 font-jost-medium">Предметы</th>
                        <th class="px-4 py-3 font-jost-medium" />
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
                        class="border-t border-slate-100"
                    >
                        <td class="px-4 py-3">{{ item.id }}</td>
                        <td class="px-4 py-3">{{ clientName(item.client_id) }}</td>
                        <td class="px-4 py-3">{{ statusLabel(item.status) }}</td>
                        <td class="px-4 py-3">
                            {{ BILLING_LABELS[item.billing_type] || item.billing_type }}
                        </td>
                        <td class="px-4 py-3">
                            {{ URGENCY_LABELS[item.urgency] || item.urgency }}
                        </td>
                        <td class="px-4 py-3">{{ kindsSummary(item) }}</td>
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
    </div>
</template>
