<script>
import AppNavIcon from "@shared/layout/AppNavIcon.vue";
import { dashboardService } from "../services/DashboardService.js";

const ATTENTION_TILES = [
    {
        key: "created",
        label: "Назначить мастера",
        hint: "Новые заказы",
        status: "created",
        tone: "action",
    },
    {
        key: "works_completed",
        label: "Калькуляция",
        hint: "Работы выполнены",
        status: "works_completed",
        tone: "action",
    },
    {
        key: "ready",
        label: "Выдать",
        hint: "Готовы к выдаче",
        status: "ready",
        tone: "action",
    },
    {
        key: "in_progress",
        label: "В работе",
        hint: "У мастеров",
        status: "in_progress",
        tone: "info",
    },
];

const QUICK_ACTIONS = [
    {
        key: "order-create",
        context: "Заказы",
        label: "Новый заказ",
        icon: "orders",
        route: { name: "manager.orders.create" },
        primary: true,
    },
    {
        key: "orders",
        context: "Заказы",
        label: "Все заказы",
        icon: "orders",
        route: { name: "manager.orders" },
        primary: false,
    },
    {
        key: "finance",
        context: "Финансы",
        label: "Касса и цели",
        icon: "finance",
        route: { name: "manager.finance" },
        primary: false,
    },
    {
        key: "warehouse",
        context: "Склад",
        label: "Склад",
        icon: "warehouse",
        route: { name: "manager.warehouse" },
        primary: false,
    },
];

export default {
    name: "DashboardPage",
    components: { AppNavIcon },
    data() {
        return {
            loading: false,
            error: null,
            attention: {
                created: 0,
                works_completed: 0,
                ready: 0,
                in_progress: 0,
            },
            balance: "0.00",
            goals: [],
            tiles: ATTENTION_TILES,
            quickActions: QUICK_ACTIONS,
        };
    },
    async mounted() {
        await this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            this.error = null;
            try {
                const data = await dashboardService.get();
                this.attention = data.attention || this.attention;
                this.balance = data.finance?.balance ?? "0.00";
                this.goals = data.goals || [];
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось загрузить дашборд";
            } finally {
                this.loading = false;
            }
        },
        countFor(tile) {
            return Number(this.attention[tile.key] || 0);
        },
        openOrders(status) {
            this.$router.push({
                name: "manager.orders",
                query: { status },
            });
        },
        openFinance() {
            this.$router.push({ name: "manager.finance" });
        },
        runQuickAction(action) {
            this.$router.push(action.route);
        },
        formatMoney(value) {
            return `${value ?? "0.00"} ₽`;
        },
        progressWidth(goal) {
            const percent = Number(goal.percent || 0);
            return `${Math.max(0, Math.min(100, percent))}%`;
        },
    },
};
</script>

<template>
    <div class="app-page">
        <h1 class="app-page-title">Дашборд</h1>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>

        <section
            class="flex flex-col gap-3 border border-slate-300 bg-white p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-4"
        >
            <div>
                <p class="text-xs text-slate-500">Баланс кассы</p>
                <p
                    class="text-lg font-jost-bold text-dark-blue-500 sm:text-xl"
                >
                    {{ formatMoney(balance) }}
                </p>
            </div>
            <button
                type="button"
                class="app-btn-secondary w-full sm:w-auto"
                @click="openFinance"
            >
                Финансы
            </button>
        </section>

        <section
            v-if="goals.length"
            class="w-full space-y-3 border border-slate-300 bg-white p-3 shadow-sm sm:p-4"
        >
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-jost-bold text-dark-blue-500">
                    Активные цели
                </h2>
                <button
                    type="button"
                    class="shrink-0 text-sm text-pink-600 hover:underline"
                    @click="openFinance"
                >
                    Все цели
                </button>
            </div>
            <div v-for="goal in goals" :key="goal.id" class="space-y-1.5">
                <div
                    class="flex flex-col gap-0.5 text-sm sm:flex-row sm:flex-wrap sm:items-baseline sm:justify-between sm:gap-2"
                >
                    <span class="font-jost-medium text-dark-blue-500">
                        {{ goal.title || `Цель #${goal.id}` }}
                    </span>
                    <span class="text-xs text-slate-600 sm:text-sm">
                        {{ formatMoney(goal.net) }}
                        /
                        {{ formatMoney(goal.target_amount) }}
                        · {{ goal.percent }}%
                    </span>
                </div>
                <div class="h-2.5 overflow-hidden bg-slate-100 sm:h-3">
                    <div
                        class="h-full bg-pink-500"
                        :style="{ width: progressWidth(goal) }"
                    />
                </div>
            </div>
        </section>

        <p
            v-else-if="!loading"
            class="border border-slate-300 bg-white px-3 py-4 text-sm leading-relaxed text-slate-500 shadow-sm"
        >
            Активных целей нет.
            <button
                type="button"
                class="mt-1 block text-pink-600 hover:underline sm:mt-0 sm:inline"
                @click="openFinance"
            >
                Поставить на экране финансов
            </button>
        </p>

        <section class="space-y-2 sm:space-y-3">
            <h2 class="text-base font-jost-bold text-dark-blue-500">
                Быстрые действия
            </h2>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2">
                <button
                    v-for="action in quickActions"
                    :key="action.key"
                    type="button"
                    class="inline-flex min-h-11 items-center justify-center gap-2 px-3 py-2.5 text-sm transition sm:justify-start sm:px-4"
                    :class="[
                        action.primary
                            ? 'col-span-2 bg-pink-500 text-white hover:bg-pink-600 sm:col-span-1'
                            : 'border border-pink-600 text-pink-700 hover:bg-pink-50',
                        'w-full sm:w-auto',
                    ]"
                    @click="runQuickAction(action)"
                >
                    <AppNavIcon :name="action.icon" :active="action.primary" />
                    <span class="font-jost-medium">{{ action.label }}</span>
                </button>
            </div>
        </section>

        <section class="space-y-2 sm:space-y-3">
            <h2 class="text-base font-jost-bold text-dark-blue-500">
                Очередь внимания
            </h2>
            <div class="grid grid-cols-2 gap-2 sm:gap-3 xl:grid-cols-4">
                <button
                    v-for="tile in tiles"
                    :key="tile.key"
                    type="button"
                    class="min-h-[5.5rem] border border-slate-300 bg-white p-3 text-left shadow-sm transition active:border-pink-400 hover:border-pink-400"
                    @click="openOrders(tile.status)"
                >
                    <p class="text-xs leading-snug text-slate-500">
                        {{ tile.hint }}
                    </p>
                    <p
                        class="mt-1 text-xl font-jost-bold sm:text-2xl"
                        :class="
                            tile.tone === 'action' && countFor(tile) > 0
                                ? 'text-pink-600'
                                : 'text-dark-blue-500'
                        "
                    >
                        {{ countFor(tile) }}
                    </p>
                    <p
                        class="mt-0.5 text-xs font-jost-medium leading-snug text-dark-blue-500 sm:mt-1 sm:text-sm"
                    >
                        {{ tile.label }}
                    </p>
                </button>
            </div>
        </section>
    </div>
</template>
