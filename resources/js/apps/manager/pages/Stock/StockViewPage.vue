<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import { stockService } from "../../services/StockService.js";

export default {
    name: "StockViewPage",
    components: { ManagerLayout },
    data() {
        return { item: null, qty: 1, comment: "", busy: false, error: null };
    },
    async mounted() {
        this.item = await stockService.get(this.$route.params.id);
    },
    methods: {
        async receive() {
            this.busy = true;
            this.error = null;
            try {
                this.item = await stockService.receive(this.item.id, { quantity: this.qty, comment: this.comment || null });
            } catch (e) {
                this.error = e.response?.data?.message || "Ошибка прихода";
            } finally {
                this.busy = false;
            }
        },
        async writeOff() {
            this.busy = true;
            this.error = null;
            try {
                this.item = await stockService.writeOff(this.item.id, { quantity: this.qty, comment: this.comment || null });
            } catch (e) {
                this.error = e.response?.data?.message || "Ошибка списания";
            } finally {
                this.busy = false;
            }
        },
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Позиция склада</template>
        <div v-if="item" class="max-w-2xl space-y-4">
            <div class="bg-white border border-slate-200 p-6">
                <h1 class="text-2xl font-jost-bold text-dark-blue-500">{{ item.name || item.title || ("#" + item.id) }}</h1>
                <pre class="mt-4 text-xs bg-slate-50 p-4 overflow-auto">{{ item }}</pre>
            </div>
            <div class="bg-white border border-slate-200 p-6 space-y-3">
                <h2 class="font-jost-bold text-dark-blue-500">Движение</h2>
                <div v-if="error" class="bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
                <input v-model.number="qty" type="number" min="1" class="border border-slate-300 px-3 py-2 w-32" />
                <input v-model="comment" placeholder="Комментарий" class="border border-slate-300 px-3 py-2 w-full" />
                <div class="flex gap-2">
                    <button type="button" class="bg-dark-blue-500 text-white px-4 py-2 text-sm" :disabled="busy" @click="receive">Приход</button>
                    <button type="button" class="bg-pink-500 text-white px-4 py-2 text-sm" :disabled="busy" @click="writeOff">Списание</button>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
