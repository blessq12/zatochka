<script>
import { clientService } from "../../services/ClientService.js";

export default {
    name: "ClientFormPage",
        data() {
        return {
            loading: false,
            error: null,
            form: { name: "", phone: "", email: "" },
        };
    },
    computed: {
        isEdit() { return !!this.$route.params.id && this.$route.name === "manager.clients.edit"; },
        title() { return this.isEdit ? "Редактировать клиента" : "Новый клиент"; },
    },
    async mounted() {
        if (this.isEdit) {
            const c = await clientService.get(this.$route.params.id);
            this.form = { name: c.name || "", phone: c.phone || "", email: c.email || "" };
        }
    },
    methods: {
        async submit() {
            this.loading = true;
            this.error = null;
            try {
                if (this.isEdit) {
                    await clientService.update(this.$route.params.id, this.form);
                    this.$router.push({ name: "manager.clients.view", params: { id: this.$route.params.id } });
                } else {
                    const created = await clientService.create(this.form);
                    this.$router.push({ name: "manager.clients.view", params: { id: created.id } });
                }
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
            <h1 class="text-xl font-jost-bold text-dark-blue-500 mb-6">{{ title }}</h1>
            <div v-if="error" class="mb-4 bg-red-50 text-red-700 px-4 py-3 text-sm">{{ error }}</div>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm mb-1">ФИО</label>
                    <input v-model="form.name" class="w-full border border-slate-300 px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Телефон</label>
                    <input v-model="form.phone" v-maska data-maska="+7 (###) ###-##-##" required class="w-full border border-slate-300 px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input v-model="form.email" type="email" class="w-full border border-slate-300 px-3 py-2" />
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading">Сохранить</button>
                    <router-link :to="{ name: 'manager.clients' }" class="px-4 py-2 text-sm text-slate-600">Отмена</router-link>
                </div>
            </form>
        </div>
</template>
