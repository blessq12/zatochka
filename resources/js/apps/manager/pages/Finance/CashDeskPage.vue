<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import { financeService } from "../../services/FinanceService.js";

export default {
    name: "CashDeskPage",
    components: { ManagerLayout },
    data() {
        return {
            summary: { in: "0", out: "0", balance: "0" },
            form: { type: "in", amount: "", comment: "" },
            loading: false,
            error: null,
        };
    },
    async mounted() { await this.reload(); },
    methods: {
        async reload() {
            const data = await financeService.listCashOperations();
            this.summary = data.summary || this.summary;
        },
        async submit() {
            this.loading = true;
            this.error = null;
            try {
                await financeService.createCashOperation(this.form);
                this.form.amount = "";
                this.form.comment = "";
                await this.reload();
            } catch (e) {
                this.error = e.response?.data?.message || "Ошибка";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Касса</template>
        <div class="grid lg:grid-cols-2 gap-6 max-w-4xl">
            <div class="bg-white border border-slate-200 p-6 space-y-3">
                <h1 class="text-xl font-jost-bold text-dark-blue-500">Сводка</h1>
                <div class="text-sm">Приход: <strong>{{ summary.in }}</strong></div>
                <div class="text-sm">Расход: <strong>{{ summary.out }}</strong></div>
                <div class="text-lg font-jost-bold">Баланс: {{ summary.balance }}</div>
                <router-link :to="{ name: 'manager.cash-operations' }" class="text-pink-500 text-sm inline-block mt-2">Все операции →</router-link>
            </div>
            <div class="bg-white border border-slate-200 p-6">
                <h2 class="text-xl font-jost-bold text-dark-blue-500 mb-4">Новая операция</h2>
                <div v-if="error" class="mb-3 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
                <form class="space-y-3" @submit.prevent="submit">
                    <select v-model="form.type" class="w-full border border-slate-300 px-3 py-2">
                        <option value="in">Приход</option>
                        <option value="out">Расход</option>
                    </select>
                    <input v-model="form.amount" required placeholder="Сумма" class="w-full border border-slate-300 px-3 py-2" />
                    <input v-model="form.comment" placeholder="Комментарий" class="w-full border border-slate-300 px-3 py-2" />
                    <button type="submit" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading">Зарегистрировать</button>
                </form>
            </div>
        </div>
    </ManagerLayout>
</template>
