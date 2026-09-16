<script>
import { equipmentService } from "../../services/EquipmentService.js";
import { clientService } from "../../services/ClientService.js";

export default {
    name: "EquipmentCreatePage",
        data() {
        return {
            clients: [],
            loading: false,
            error: null,
            form: {
                clientId: "",
                title: "",
                brand: "",
                modelName: "",
                equipmentType: "clipper",
                parts: [{ name: "", serialNumber: "" }],
            },
        };
    },
    async mounted() {
        const data = await clientService.list();
        this.clients = data.items || [];
    },
    methods: {
        async submit() {
            this.loading = true;
            this.error = null;
            try {
                const payload = {
                    ...this.form,
                    clientId: this.form.clientId ? Number(this.form.clientId) : null,
                    parts: this.form.parts.filter((p) => p.name),
                };
                const item = await equipmentService.create(payload);
                this.$router.push({ name: "manager.equipment.view", params: { id: item.id } });
            } catch (e) {
                this.error = e.response?.data?.message || "Не удалось сохранить";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <div class="max-w-xl bg-white border border-slate-200 p-6">
            <h1 class="text-xl font-jost-bold text-dark-blue-500 mb-6">Новое оборудование</h1>
            <div v-if="error" class="mb-4 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm mb-1">Клиент</label>
                    <select v-model="form.clientId" class="w-full border border-slate-300 px-3 py-2">
                        <option value="">Не указан</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name || "—" }} — {{ c.phone }}</option>
                    </select>
                </div>
                <div><label class="block text-sm mb-1">Название</label><input v-model="form.title" required class="w-full border border-slate-300 px-3 py-2" /></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm mb-1">Бренд</label><input v-model="form.brand" required class="w-full border border-slate-300 px-3 py-2" /></div>
                    <div><label class="block text-sm mb-1">Модель</label><input v-model="form.modelName" required class="w-full border border-slate-300 px-3 py-2" /></div>
                </div>
                <div>
                    <label class="block text-sm mb-1">Тип</label>
                    <select v-model="form.equipmentType" class="w-full border border-slate-300 px-3 py-2">
                        <option value="clipper">Машинка</option>
                        <option value="trimmer">Триммер</option>
                        <option value="shaver">Бритва</option>
                        <option value="dryer">Фен</option>
                        <option value="other">Другое</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Компонент</label>
                    <input v-model="form.parts[0].name" placeholder="Название части" class="w-full border border-slate-300 px-3 py-2 mb-2" />
                    <input v-model="form.parts[0].serialNumber" placeholder="Серийный номер" class="w-full border border-slate-300 px-3 py-2" />
                </div>
                <button type="submit" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading">Создать</button>
            </form>
        </div>
</template>
