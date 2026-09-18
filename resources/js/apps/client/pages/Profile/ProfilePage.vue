<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "ClientProfilePage",
    data() {
        return {
            form: {
                full_name: "",
                phone: "",
                birth_date: "",
                delivery_address: "",
            },
            saving: false,
            error: null,
            success: null,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    mounted() {
        this.syncForm();
    },
    methods: {
        syncForm() {
            const user = this.authStore.user || {};
            this.form = {
                full_name: user.full_name || "",
                phone: user.phone || "",
                birth_date: user.birth_date || "",
                delivery_address: user.delivery_address || "",
            };
        },
        async save() {
            this.saving = true;
            this.error = null;
            this.success = null;
            try {
                const result = await this.authStore.updateClient(this.form);
                if (!result.success) {
                    this.error = result.error;
                    return;
                }
                this.syncForm();
                this.success = "Профиль сохранён";
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<template>
    <div class="app-page">
        <div class="app-page-header">
            <h1 class="app-page-title">Профиль</h1>
        </div>

        <section class="app-panel max-w-xl space-y-3">
            <p class="text-base text-slate-600">
                Email: {{ authStore.user?.email || "—" }}
            </p>
            <p v-if="error" class="text-base text-red-600">{{ error }}</p>
            <p v-if="success" class="text-base text-green-700">{{ success }}</p>

            <label class="block text-base">
                Имя
                <input v-model="form.full_name" type="text" class="app-field mt-1" />
            </label>
            <label class="block text-base">
                Телефон
                <input v-model="form.phone" type="tel" class="app-field mt-1" />
            </label>
            <label class="block text-base">
                Дата рождения
                <input
                    v-model="form.birth_date"
                    type="date"
                    class="app-field mt-1"
                />
            </label>
            <label class="block text-base">
                Адрес доставки
                <input
                    v-model="form.delivery_address"
                    type="text"
                    class="app-field mt-1"
                />
            </label>

            <button
                type="button"
                class="app-btn-primary"
                :disabled="saving"
                @click="save"
            >
                Сохранить
            </button>
        </section>
    </div>
</template>
