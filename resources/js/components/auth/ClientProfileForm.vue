<template>
    <div class="client-profile-form">
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- ФИО -->
            <div>
                <label
                    for="full_name"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    ФИО *
                </label>
                <input
                    id="full_name"
                    v-model="form.full_name"
                    type="text"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="Иванов Иван Иванович"
                    :disabled="loading"
                />
                <div v-if="errors.full_name" class="mt-1 text-sm text-red-600">
                    {{ errors.full_name }}
                </div>
            </div>

            <!-- Telegram -->
            <div>
                <label
                    for="telegram"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Telegram аккаунт
                </label>
                <input
                    id="telegram"
                    v-model="form.telegram"
                    type="text"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="@username или номер телефона"
                    :disabled="loading"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Для получения уведомлений и верификации аккаунта
                </p>
                <div v-if="errors.telegram" class="mt-1 text-sm text-red-600">
                    {{ errors.telegram }}
                </div>
            </div>

            <!-- Дата рождения -->
            <div>
                <label
                    for="birth_date"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Дата рождения
                </label>
                <input
                    id="birth_date"
                    v-model="form.birth_date"
                    type="date"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    :disabled="loading"
                />
                <div v-if="errors.birth_date" class="mt-1 text-sm text-red-600">
                    {{ errors.birth_date }}
                </div>
            </div>

            <!-- Адрес доставки -->
            <div>
                <label
                    for="delivery_address"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Адрес доставки
                </label>
                <textarea
                    id="delivery_address"
                    v-model="form.delivery_address"
                    rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-accent focus:border-accent dark:bg-gray-700 dark:text-white"
                    placeholder="Улица, дом, квартира"
                    :disabled="loading"
                ></textarea>
                <div
                    v-if="errors.delivery_address"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ errors.delivery_address }}
                </div>
            </div>

            <!-- Кнопка сохранения -->
            <button
                type="submit"
                :disabled="loading"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-accent hover:bg-accent/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
                <span v-if="loading" class="flex items-center">
                    <i class="mdi mdi-loading mdi-spin mr-2"></i>
                    Сохранение...
                </span>
                <span v-else>Сохранить изменения</span>
            </button>
        </form>
    </div>
</template>

<script>
import clientAuthService from "../../services/clientAuthService.js";

export default {
    name: "ClientProfileForm",
    props: {
        client: {
            type: Object,
            required: true,
        },
    },
    emits: ["profile-updated"],
    data() {
        return {
            form: {
                full_name: "",
                telegram: "",
                birth_date: "",
                delivery_address: "",
            },
            errors: {},
            loading: false,
        };
    },
    mounted() {
        this.initializeForm();
    },
    methods: {
        initializeForm() {
            this.form = {
                full_name: this.client?.full_name || "",
                telegram: this.client?.telegram || "",
                birth_date: this.client?.birth_date || "",
                delivery_address: this.client?.delivery_address || "",
            };
        },

        async handleSubmit() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await clientAuthService.updateProfile(this.form);
                
                this.$emit("profile-updated", response.data.client);
                
                // Показываем уведомление об успешном обновлении
                if (window.modalService) {
                    window.modalService.alert(
                        "Профиль обновлен",
                        "Ваши данные успешно сохранены",
                        "success"
                    );
                }
            } catch (error) {
                console.error("Profile update error:", error);
                
                // Обрабатываем ошибки валидации
                if (error.message.includes("validation")) {
                    this.errors.general =
                        "Проверьте правильность заполнения полей";
                } else {
                    this.errors.general =
                        error.message || "Произошла ошибка при обновлении профиля";
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
