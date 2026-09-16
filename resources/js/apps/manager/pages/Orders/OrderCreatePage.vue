<script>
import { orderService } from "../../services/OrderService.js";
import { clientService } from "../../services/ClientService.js";

export default {
    name: "OrderCreatePage",
        data() {
        return {
            clients: [],
            loading: false,
            error: null,
            form: {
                clientId: "",
                estimatedAmount: "0",
                serviceType: "sharpening",
                billingType: "paid",
                urgency: "normal",
                deliveryRequired: false,
                defects: "",
                internalNotes: "",
                items: [{ toolName: "", toolType: "knife", quantity: 1 }],
            },
        };
    },
    async mounted() {
        const data = await clientService.list();
        this.clients = data.items || [];
    },
    methods: {
        addItem() {
            this.form.items.push({ toolName: "", toolType: "knife", quantity: 1 });
        },
        async submit() {
            this.loading = true;
            this.error = null;
            try {
                const payload = {
                    ...this.form,
                    clientId: this.form.clientId ? Number(this.form.clientId) : null,
                    items: this.form.items.map((i) => ({
                        toolName: i.toolName || null,
                        toolType: i.toolType || null,
                        quantity: Number(i.quantity) || 1,
                    })),
                };
                const order = await orderService.create(payload);
                this.$router.push({ name: "manager.orders.view", params: { id: order.id } });
            } catch (e) {
                this.error = e.response?.data?.message || "Не удалось создать заказ";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <div class="max-w-2xl bg-white border border-slate-200 p-6">
            <h1 class="text-xl font-jost-bold text-dark-blue-500 mb-6">Новый заказ</h1>
            <div v-if="error" class="mb-4 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm mb-1">Клиент</label>
                    <select v-model="form.clientId" class="w-full border border-slate-300 px-3 py-2">
                        <option value="">Без клиента</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">
                            {{ c.name || "—" }} — {{ c.phone }}
                        </option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Тип услуги</label>
                        <select v-model="form.serviceType" class="w-full border border-slate-300 px-3 py-2">
                            <option value="sharpening">Заточка</option>
                            <option value="repair">Ремонт</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Срочность</label>
                        <select v-model="form.urgency" class="w-full border border-slate-300 px-3 py-2">
                            <option value="normal">Обычная</option>
                            <option value="urgent">Срочная</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Оплата</label>
                        <select v-model="form.billingType" class="w-full border border-slate-300 px-3 py-2">
                            <option value="paid">Платный</option>
                            <option value="warranty">Гарантия</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Ориентировочная сумма</label>
                        <input v-model="form.estimatedAmount" required class="w-full border border-slate-300 px-3 py-2" />
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-sm font-jost-medium">Позиции</label>
                        <button type="button" class="text-sm text-pink-500" @click="addItem">+ позиция</button>
                    </div>
                    <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-3 gap-2 mb-2">
                        <input v-model="item.toolName" placeholder="Название" class="border border-slate-300 px-3 py-2" />
                        <input v-model="item.toolType" placeholder="Тип" class="border border-slate-300 px-3 py-2" />
                        <input v-model.number="item.quantity" type="number" min="1" class="border border-slate-300 px-3 py-2" />
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading">Создать</button>
                    <router-link :to="{ name: 'manager.orders' }" class="px-4 py-2 text-sm text-slate-600">Отмена</router-link>
                </div>
            </form>
        </div>
</template>
