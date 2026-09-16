<script>
import { mapStores } from "pinia";
import { useManagerStore } from "../stores/managerStore.js";

export default {
    name: "ManagerLoginForm",
    data() {
        return {
            form: { email: "", password: "" },
        };
    },
    computed: {
        ...mapStores(useManagerStore),
    },
    methods: {
        async submit() {
            const result = await this.managerStore.login(this.form);
            if (result.success) {
                this.$router.replace({ name: "manager.dashboard" });
            }
        },
    },
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-dark-blue-500 px-4">
        <form
            class="w-full max-w-md bg-white p-8 space-y-5 shadow-xl"
            @submit.prevent="submit"
        >
            <div>
                <h1 class="text-2xl font-jost-bold text-dark-blue-500">Вход менеджера</h1>
                <p class="text-sm text-slate-500 mt-1">Панель управления Заточка.ТСК</p>
            </div>
            <div v-if="managerStore.error" class="bg-red-50 text-red-700 px-4 py-3 text-sm">
                {{ managerStore.error }}
            </div>
            <div>
                <label class="block text-sm font-jost-medium text-slate-700 mb-2">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500/40"
                />
            </div>
            <div>
                <label class="block text-sm font-jost-medium text-slate-700 mb-2">Пароль</label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500/40"
                />
            </div>
            <button
                type="submit"
                class="w-full bg-pink-500 hover:bg-pink-600 text-white font-jost-bold py-3 disabled:opacity-50"
                :disabled="managerStore.isLoading"
            >
                {{ managerStore.isLoading ? "Вход..." : "Войти" }}
            </button>
        </form>
    </div>
</template>
