<script>
import ManagerLayout from "../../components/Layout/ManagerLayout.vue";
import { orderService } from "../../services/OrderService.js";

export default {
    name: "OrderViewPage",
    components: { ManagerLayout },
    data() {
        return { order: null, busy: false, error: null };
    },
    async mounted() {
        await this.reload();
    },
    methods: {
        async reload() {
            this.order = await orderService.get(this.$route.params.id);
        },
        async run(action) {
            this.busy = true;
            this.error = null;
            try {
                if (action === "cancel") {
                    const reason = prompt("Причина отмены");
                    if (!reason) return;
                    await orderService.cancel(this.order.id, reason);
                } else if (action === "close") {
                    await orderService.close(this.order.id);
                } else if (action === "issue") {
                    await orderService.issue(this.order.id);
                }
                await this.reload();
            } catch (e) {
                this.error = e.response?.data?.message || "Ошибка действия";
            } finally {
                this.busy = false;
            }
        },
    },
};
</script>

<template>
    <ManagerLayout>
        <template #title>Заказ</template>
        <div v-if="order" class="space-y-4 max-w-3xl">
            <div class="bg-white border border-slate-200 p-6">
                <div class="flex justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-jost-bold text-dark-blue-500">Заказ {{ order.number || order.id }}</h1>
                        <p class="text-sm text-slate-500 mt-1">Статус: {{ order.status }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 justify-end">
                        <button type="button" class="px-3 py-2 text-sm bg-dark-blue-500 text-white" :disabled="busy" @click="run('issue')">Выдать</button>
                        <button type="button" class="px-3 py-2 text-sm bg-slate-700 text-white" :disabled="busy" @click="run('close')">Закрыть</button>
                        <button type="button" class="px-3 py-2 text-sm bg-red-600 text-white" :disabled="busy" @click="run('cancel')">Отменить</button>
                    </div>
                </div>
                <div v-if="error" class="mt-4 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
                <pre class="mt-6 text-xs bg-slate-50 p-4 overflow-auto max-h-[480px]">{{ order }}</pre>
            </div>
            <router-link :to="{ name: 'manager.orders' }" class="text-sm text-pink-500">← К списку</router-link>
        </div>
    </ManagerLayout>
</template>
