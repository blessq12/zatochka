<script>
import { staffUserService } from "../../services/StaffUserService.js";

export default {
    name: "UserFormPage",
        data() {
        return {
            loading: false,
            error: null,
            form: { name: "", email: "", role: "manager", password: "" },
        };
    },
    computed: {
        isEdit() {
            return !!this.$route.params.id;
        },
        title() {
            return this.isEdit ? "Редактировать сотрудника" : "Новый сотрудник";
        },
    },
    async mounted() {
        if (this.isEdit) {
            const user = await staffUserService.get(this.$route.params.id);
            this.form = { name: user.name, email: user.email, role: user.role, password: "" };
        }
    },
    methods: {
        async submit() {
            this.loading = true;
            this.error = null;
            try {
                if (this.isEdit) {
                    const payload = { name: this.form.name, email: this.form.email, role: this.form.role };
                    if (this.form.password) payload.password = this.form.password;
                    await staffUserService.update(this.$route.params.id, payload);
                } else {
                    await staffUserService.create(this.form);
                }
                this.$router.push({ name: "manager.users" });
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
                    <label class="block text-sm mb-1">Имя</label>
                    <input v-model="form.name" required class="w-full border border-slate-300 px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Эл. почта</label>
                    <input v-model="form.email" type="email" required class="w-full border border-slate-300 px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Роль</label>
                    <select v-model="form.role" class="w-full border border-slate-300 px-3 py-2">
                        <option value="manager">Менеджер</option>
                        <option value="master">Мастер</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Пароль</label>
                    <input v-model="form.password" type="password" :required="!isEdit" minlength="8" class="w-full border border-slate-300 px-3 py-2" />
                    <p v-if="isEdit" class="text-xs text-slate-500 mt-1">Оставьте пустым, если не нужно менять</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-pink-500 text-white px-4 py-2 font-jost-bold text-sm" :disabled="loading">
                        Сохранить
                    </button>
                    <router-link :to="{ name: 'manager.users' }" class="px-4 py-2 text-sm text-slate-600">Отмена</router-link>
                </div>
            </form>
        </div>
</template>
