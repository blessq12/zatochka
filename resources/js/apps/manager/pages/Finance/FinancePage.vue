<script>
import { financeService } from "../../services/FinanceService.js";

export default {
    name: "FinancePage",
    data() {
        const today = new Date().toISOString().slice(0, 10);
        const monthLater = new Date();
        monthLater.setMonth(monthLater.getMonth() + 1);
        return {
            loading: false,
            error: null,
            entries: [],
            summary: {
                income: "0.00",
                expense: "0.00",
                net: "0.00",
                balance: "0.00",
            },
            filterType: "",
            goals: [],
            cashForm: {
                type: "income",
                amount: "",
                comment: "",
            },
            savingCash: false,
            goalForm: {
                title: "",
                target_amount: "",
                starts_at: today,
                ends_at: monthLater.toISOString().slice(0, 10),
            },
            savingGoal: false,
        };
    },
    async mounted() {
        await this.reload();
    },
    computed: {
        activeGoals() {
            return (this.goals || []).filter((goal) => goal.status === "active");
        },
    },
    methods: {
        async reload() {
            this.loading = true;
            this.error = null;
            try {
                const [cash, goals] = await Promise.all([
                    financeService.listCashEntries({
                        type: this.filterType || null,
                    }),
                    financeService.listGoals(),
                ]);
                this.entries = cash.items || [];
                this.summary = cash.summary || this.summary;
                this.goals = goals;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось загрузить финансы";
            } finally {
                this.loading = false;
            }
        },
        async submitCash() {
            if (this.cashForm.amount === "" || this.cashForm.amount == null) {
                this.error = "Укажите сумму";
                return;
            }
            this.savingCash = true;
            this.error = null;
            try {
                await financeService.createCashEntry({
                    type: this.cashForm.type,
                    amount: Number(this.cashForm.amount),
                    comment: this.cashForm.comment || null,
                });
                this.cashForm.amount = "";
                this.cashForm.comment = "";
                await this.reload();
            } catch (e) {
                this.error =
                    e.response?.data?.message ||
                    "Не удалось сохранить операцию";
            } finally {
                this.savingCash = false;
            }
        },
        async removeEntry(entry) {
            if (entry.source !== "manual") {
                return;
            }
            this.error = null;
            try {
                await financeService.deleteCashEntry(entry.id);
                await this.reload();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось удалить операцию";
            }
        },
        async submitGoal() {
            if (
                !this.goalForm.target_amount ||
                !this.goalForm.starts_at ||
                !this.goalForm.ends_at
            ) {
                this.error = "Укажите сумму цели, дату старта и окончания";
                return;
            }
            this.savingGoal = true;
            this.error = null;
            try {
                await financeService.createGoal({
                    title: this.goalForm.title || null,
                    target_amount: Number(this.goalForm.target_amount),
                    starts_at: this.goalForm.starts_at,
                    ends_at: this.goalForm.ends_at,
                });
                this.goalForm.title = "";
                this.goalForm.target_amount = "";
                await this.reload();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось создать цель";
            } finally {
                this.savingGoal = false;
            }
        },
        async cancelGoal(goal) {
            this.error = null;
            try {
                await financeService.cancelGoal(goal.id);
                await this.reload();
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Не удалось отменить цель";
            }
        },
        typeLabel(type) {
            return type === "income" ? "Приход" : "Расход";
        },
        sourceLabel(source) {
            return source === "order_issue" ? "Заказ" : "Вручную";
        },
        formatMoney(value) {
            return `${value ?? "0.00"} ₽`;
        },
        progressWidth(goal) {
            const percent = Number(goal.progress?.percent || 0);
            return `${Math.max(0, Math.min(100, percent))}%`;
        },
        formatDate(value) {
            if (!value) {
                return "—";
            }
            return new Date(value).toLocaleDateString("ru-RU");
        },
    },
};
</script>

<template>
    <div class="app-page">
        <h1 class="app-page-title">Финансы</h1>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-if="loading" class="text-sm text-slate-500">Загрузка…</p>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="border border-slate-300 bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Баланс кассы</p>
                <p class="font-jost-medium text-dark-blue-500">
                    {{ formatMoney(summary.balance) }}
                </p>
            </div>
            <div class="border border-slate-300 bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Приходы</p>
                <p class="font-jost-medium text-dark-blue-500">
                    {{ formatMoney(summary.income) }}
                </p>
            </div>
            <div class="border border-slate-300 bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Расходы</p>
                <p class="font-jost-medium text-dark-blue-500">
                    {{ formatMoney(summary.expense) }}
                </p>
            </div>
            <div class="border border-slate-300 bg-white p-3 shadow-sm">
                <p class="text-xs text-slate-500">Чистыми</p>
                <p class="font-jost-medium text-dark-blue-500">
                    {{ formatMoney(summary.net) }}
                </p>
            </div>
        </div>

        <section
            v-if="activeGoals.length"
            class="w-full space-y-3 border border-slate-300 bg-white p-3 shadow-sm"
        >
            <div
                v-for="goal in activeGoals"
                :key="goal.id"
                class="space-y-1"
            >
                <div
                    class="flex flex-wrap items-baseline justify-between gap-2 text-sm"
                >
                    <span class="font-jost-medium text-dark-blue-500">
                        {{ goal.title || `Цель #${goal.id}` }}
                    </span>
                    <span class="text-slate-600">
                        {{ formatMoney(goal.progress?.net) }}
                        /
                        {{ formatMoney(goal.target_amount) }}
                        · {{ goal.progress?.percent ?? 0 }}%
                    </span>
                </div>
                <div class="h-3 overflow-hidden bg-slate-100">
                    <div
                        class="h-full bg-pink-500 transition-[width]"
                        :style="{ width: progressWidth(goal) }"
                    />
                </div>
            </div>
        </section>

        <div
            class="grid gap-6 lg:grid-cols-[minmax(16rem,22rem)_minmax(0,1fr)] lg:items-start"
        >
            <aside class="space-y-4 lg:sticky lg:top-4">
                <section
                    class="space-y-3 border border-slate-300 bg-white p-3 shadow-sm"
                >
                    <h2 class="text-sm font-jost-bold text-dark-blue-500">
                        Операция
                    </h2>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Тип</span>
                        <select v-model="cashForm.type" class="app-field">
                            <option value="income">Внесение (приход)</option>
                            <option value="expense">Списание (расход)</option>
                        </select>
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Сумма</span>
                        <input
                            v-model="cashForm.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="app-field"
                        />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Комментарий</span>
                        <input
                            v-model="cashForm.comment"
                            type="text"
                            class="app-field"
                            placeholder="Необязательно"
                        />
                    </label>
                    <button
                        type="button"
                        class="app-btn-primary w-full"
                        :disabled="savingCash"
                        @click="submitCash"
                    >
                        {{ savingCash ? "Сохраняю…" : "Провести" }}
                    </button>
                </section>

                <section
                    class="space-y-3 border border-slate-300 bg-white p-3 shadow-sm"
                >
                    <h2 class="text-sm font-jost-bold text-dark-blue-500">
                        Цели по заработку
                    </h2>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Название</span>
                        <input
                            v-model="goalForm.title"
                            type="text"
                            class="app-field"
                            placeholder="Необязательно"
                        />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Сумма цели</span>
                        <input
                            v-model="goalForm.target_amount"
                            type="number"
                            min="0"
                            step="0.01"
                            class="app-field"
                        />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Старт</span>
                        <input
                            v-model="goalForm.starts_at"
                            type="date"
                            class="app-field"
                        />
                    </label>
                    <label class="block space-y-1">
                        <span class="text-xs text-slate-500">Окончание</span>
                        <input
                            v-model="goalForm.ends_at"
                            type="date"
                            class="app-field"
                            :min="goalForm.starts_at"
                        />
                    </label>
                    <button
                        type="button"
                        class="app-btn-primary w-full"
                        :disabled="savingGoal"
                        @click="submitGoal"
                    >
                        {{ savingGoal ? "Создаю…" : "Поставить цель" }}
                    </button>

                    <p
                        v-if="!loading && goals.length === 0"
                        class="text-sm text-slate-500"
                    >
                        Целей пока нет
                    </p>

                    <div
                        v-for="goal in goals"
                        :key="goal.id"
                        class="space-y-2 border-t border-slate-100 pt-3"
                    >
                        <div
                            class="flex items-start justify-between gap-2"
                        >
                            <div class="min-w-0">
                                <div
                                    class="text-sm font-jost-medium text-dark-blue-500"
                                >
                                    {{ goal.title || `Цель #${goal.id}` }}
                                </div>
                                <p class="text-xs text-slate-600">
                                    {{ formatDate(goal.starts_at) }}
                                    →
                                    {{ formatDate(goal.ends_at) }}
                                </p>
                            </div>
                            <span class="shrink-0 text-xs text-slate-500">
                                {{
                                    goal.status === "active"
                                        ? "Активна"
                                        : "Отменена"
                                }}
                            </span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between gap-2">
                                <span class="text-slate-500">Цель</span>
                                <span>{{
                                    formatMoney(goal.target_amount)
                                }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-slate-500">Чистыми</span>
                                <span>{{
                                    formatMoney(goal.progress?.net)
                                }}</span>
                            </div>
                        </div>
                        <div
                            v-if="(goal.progress?.series || []).length"
                            class="space-y-1"
                        >
                            <p class="text-xs text-slate-500">Динамика</p>
                            <ul
                                class="max-h-28 space-y-0.5 overflow-y-auto text-xs text-slate-600"
                            >
                                <li
                                    v-for="point in goal.progress.series"
                                    :key="point.date"
                                    class="flex justify-between gap-2"
                                >
                                    <span>{{ point.date }}</span>
                                    <span>{{ formatMoney(point.net) }}</span>
                                </li>
                            </ul>
                        </div>
                        <button
                            v-if="goal.status === 'active'"
                            type="button"
                            class="app-btn-secondary w-full"
                            @click="cancelGoal(goal)"
                        >
                            Отменить цель
                        </button>
                    </div>
                </section>
            </aside>

            <section class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-base font-jost-bold text-dark-blue-500">
                        Операции
                    </h2>
                    <select
                        v-model="filterType"
                        class="app-field w-full sm:w-40"
                        @change="reload"
                    >
                        <option value="">Все</option>
                        <option value="income">Приходы</option>
                        <option value="expense">Расходы</option>
                    </select>
                </div>
                <p
                    v-if="!loading && entries.length === 0"
                    class="border border-slate-300 bg-white px-3 py-4 text-sm text-slate-500 shadow-sm"
                >
                    Операций пока нет
                </p>
                <div
                    v-for="entry in entries"
                    :key="entry.id"
                    class="flex flex-col gap-2 border border-slate-300 bg-white p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="min-w-0 space-y-1 text-sm">
                        <div class="font-jost-medium text-dark-blue-500">
                            {{ typeLabel(entry.type) }}
                            ·
                            {{ formatMoney(entry.amount) }}
                        </div>
                        <p class="text-xs text-slate-500">
                            {{ sourceLabel(entry.source) }}
                            <template v-if="entry.order_id">
                                · заказ #{{ entry.order_id }}
                            </template>
                            ·
                            {{
                                new Date(entry.occurred_at).toLocaleString(
                                    "ru-RU",
                                )
                            }}
                        </p>
                        <p
                            v-if="entry.comment"
                            class="text-sm text-slate-600"
                        >
                            {{ entry.comment }}
                        </p>
                    </div>
                    <button
                        v-if="entry.source === 'manual'"
                        type="button"
                        class="app-btn-secondary w-full sm:w-auto"
                        @click="removeEntry(entry)"
                    >
                        Удалить
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
