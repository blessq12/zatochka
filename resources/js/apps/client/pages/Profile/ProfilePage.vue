<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

const fieldClass =
    "w-full max-w-full min-w-0 border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 shadow-lg outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200 sm:px-6 sm:py-4";

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
            fieldClass,
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
        async logout() {
            await this.authStore.logout();
            this.$router.push({ name: "client.login" });
        },
    },
};
</script>

<template>
    <div>
            <p class="mb-4 text-base text-dark-gray-500 dark:text-gray-200">
                Email: {{ authStore.user?.email || "—" }}
            </p>
            <p v-if="error" class="mb-2 text-base text-red-600 lg:mb-4 lg:text-base">
                {{ error }}
            </p>
            <p
                v-if="success"
                class="mb-2 text-base text-green-700 lg:mb-4 lg:text-base"
            >
                {{ success }}
            </p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:gap-6">
                <label class="block">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
                    >
                        ФИО
                    </span>
                    <input
                        v-model="form.full_name"
                        type="text"
                        :class="fieldClass"
                    />
                </label>
                <label class="block">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
                    >
                        Телефон
                    </span>
                    <input
                        v-model="form.phone"
                        type="tel"
                        :class="fieldClass"
                    />
                </label>
                <label class="block">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
                    >
                        Дата рождения
                    </span>
                    <input
                        v-model="form.birth_date"
                        type="date"
                        :class="fieldClass"
                    />
                </label>
                <label class="block sm:col-span-2">
                    <span
                        class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
                    >
                        Адрес доставки
                    </span>
                    <input
                        v-model="form.delivery_address"
                        type="text"
                        :class="fieldClass"
                    />
                </label>
            </div>

            <button
                type="button"
                class="mt-4 w-full bg-[#C20A6C] px-6 py-3.5 font-jost-bold text-white disabled:opacity-50"
                :disabled="saving"
                @click="save"
            >
                {{ saving ? "Сохранение…" : "Сохранить" }}
            </button>

            <div class="mt-8 space-y-3 border-t border-white/10 pt-4">
                <a
                    href="/"
                    class="block w-full py-3 text-center text-base font-jost-medium text-dark-gray-500 dark:text-gray-200"
                >
                    На сайт
                </a>
                <button
                    type="button"
                    class="w-full py-3 text-base font-jost-bold text-[#C20A6C]"
                    @click="logout"
                >
                    Выход
                </button>
            </div>
    </div>
</template>
