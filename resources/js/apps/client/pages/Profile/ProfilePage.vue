<script>
import { mapStores } from "pinia";
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "ClientProfilePage",
    components: { ClientSectionCard },
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
    <div class="space-y-6">
        <ClientSectionCard title="ДАННЫЕ ПРОФИЛЯ">
            <p class="mb-6 text-base text-dark-gray-500 dark:text-gray-200">
                Email: {{ authStore.user?.email || "—" }}
            </p>
            <p v-if="error" class="mb-4 text-base text-red-600">{{ error }}</p>
            <p v-if="success" class="mb-4 text-base text-green-700">
                {{ success }}
            </p>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <label class="block">
                    <span
                        class="mb-2 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-base"
                    >
                        ФИО
                    </span>
                    <input
                        v-model="form.full_name"
                        type="text"
                        class="w-full border border-white/20 bg-white/60 px-4 py-3 text-dark-gray-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200"
                    />
                </label>
                <label class="block">
                    <span
                        class="mb-2 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-base"
                    >
                        Телефон
                    </span>
                    <input
                        v-model="form.phone"
                        type="tel"
                        class="w-full border border-white/20 bg-white/60 px-4 py-3 text-dark-gray-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200"
                    />
                </label>
                <label class="block">
                    <span
                        class="mb-2 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-base"
                    >
                        Дата рождения
                    </span>
                    <input
                        v-model="form.birth_date"
                        type="date"
                        class="w-full border border-white/20 bg-white/60 px-4 py-3 text-dark-gray-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200"
                    />
                </label>
                <label class="block sm:col-span-2">
                    <span
                        class="mb-2 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:text-base"
                    >
                        Адрес доставки
                    </span>
                    <input
                        v-model="form.delivery_address"
                        type="text"
                        class="w-full border border-white/20 bg-white/60 px-4 py-3 text-dark-gray-500 backdrop-blur-md outline-none focus:border-[#C3006B] dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200"
                    />
                </label>
            </div>

            <button
                type="button"
                class="mt-8 bg-[#C3006B] px-8 py-3 font-jost-bold text-base text-white shadow-lg transition hover:bg-[#A8005A] disabled:opacity-50 sm:text-lg"
                :disabled="saving"
                @click="save"
            >
                {{ saving ? "Сохранение…" : "Сохранить" }}
            </button>
        </ClientSectionCard>
    </div>
</template>
