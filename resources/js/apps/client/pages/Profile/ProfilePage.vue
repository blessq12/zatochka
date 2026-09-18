<script>
import { mapStores } from "pinia";
import ClientSectionCard from "../../components/Layout/ClientSectionCard.vue";
import { useAuthStore } from "../../stores/authStore.js";

const fieldClass =
    "w-full border border-dark-blue-500/20 bg-white/60 px-3 py-2.5 text-dark-gray-500 outline-none focus:border-[#C3006B] dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200 lg:border-white/20 lg:px-4 lg:py-3 lg:backdrop-blur-md";

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
    },
};
</script>

<template>
    <div class="space-y-3 lg:space-y-6">
        <ClientSectionCard title="ДАННЫЕ ПРОФИЛЯ">
            <p class="mb-3 text-sm text-dark-gray-500 dark:text-gray-200 lg:mb-6 lg:text-base">
                Email: {{ authStore.user?.email || "—" }}
            </p>
            <p v-if="error" class="mb-2 text-sm text-red-600 lg:mb-4 lg:text-base">
                {{ error }}
            </p>
            <p
                v-if="success"
                class="mb-2 text-sm text-green-700 lg:mb-4 lg:text-base"
            >
                {{ success }}
            </p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:gap-6">
                <label class="block">
                    <span
                        class="mb-1 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
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
                        class="mb-1 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
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
                        class="mb-1 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
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
                        class="mb-1 block text-sm font-jost-medium text-dark-gray-500 dark:text-gray-200 lg:mb-2"
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
                class="mt-4 w-full bg-[#C3006B] px-6 py-2.5 font-jost-bold text-white transition hover:bg-[#A8005A] disabled:opacity-50 lg:mt-8 lg:w-auto lg:px-8 lg:py-3 lg:text-lg"
                :disabled="saving"
                @click="save"
            >
                {{ saving ? "Сохранение…" : "Сохранить" }}
            </button>
        </ClientSectionCard>
    </div>
</template>
