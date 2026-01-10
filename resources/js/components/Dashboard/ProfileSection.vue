<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import * as yup from "yup";

export default {
    name: "ProfileSection",
    data() {
        return {
            isEditing: false,
            form: {
                full_name: "",
                phone: "",
                email: "",
                birth_date: "",
                delivery_address: "",
                telegram: "",
            },
            errors: {},
            schema: yup.object().shape({
                full_name: yup
                    .string()
                    .required("ФИО обязательно для заполнения")
                    .min(2, "ФИО должно содержать минимум 2 символа"),
                phone: yup
                    .string()
                    .required("Телефон обязателен для заполнения")
                    .min(18, "Номер телефона должен быть 18 символов")
                    .max(18, "Номер телефона должен быть 18 символов"),
                email: yup
                    .string()
                    .email("Неверный формат email")
                    .nullable(),
                birth_date: yup.date().nullable(),
                delivery_address: yup.string().nullable(),
                telegram: yup.string().nullable(),
            }),
            isSaving: false,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
        user() {
            return this.authStore.user || {};
        },
        bonusAccount() {
            return this.authStore.bonusAccount || {};
        },
    },
    watch: {
        user: {
            immediate: true,
            handler(newUser) {
                if (newUser && !this.isEditing) {
                    this.initForm();
                }
            },
        },
    },
    methods: {
        initForm() {
            this.form = {
                full_name: this.user.full_name || "",
                phone: this.user.phone || "",
                email: this.user.email || "",
                birth_date: this.user.birth_date || "",
                delivery_address: this.user.delivery_address || "",
                telegram: this.user.telegram || "",
            };
        },
        startEdit() {
            this.isEditing = true;
            this.initForm();
            this.errors = {};
        },
        cancelEdit() {
            this.isEditing = false;
            this.initForm();
            this.errors = {};
        },
        async handleSubmit() {
            this.errors = {};
            this.isSaving = true;

            try {
                await this.schema.validate(this.form, {
                    abortEarly: false,
                });

                // Убираем @ из telegram, если есть
                const formData = { ...this.form };
                if (formData.telegram) {
                    formData.telegram = formData.telegram.replace(/^@/, "");
                }

                const result = await this.authStore.updateClient(formData);

                if (result.success) {
                    this.isEditing = false;
                } else {
                    this.errors.general = result.error;
                }
            } catch (error) {
                if (error.inner) {
                    error.inner.forEach((err) => {
                        this.errors[err.path] = err.message;
                    });
                } else {
                    this.errors.general = error.message;
                }
            } finally {
                this.isSaving = false;
            }
        },
        formatDate(dateString) {
            if (!dateString) return "";
            const date = new Date(dateString);
            return date.toLocaleDateString("ru-RU", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
            });
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <!-- Информация о бонусном счете -->
        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
        >
            <h2
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] text-center whitespace-nowrap"
            >
                БОНУСНЫЙ СЧЕТ
            </h2>
            <div class="text-center mt-4">
                <p
                    class="text-3xl sm:text-4xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-2"
                >
                    {{ bonusAccount.balance || 0 }} ₽
                </p>
                <p
                    class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200"
                >
                    Доступно к списанию
                </p>
            </div>
        </div>

        <!-- Форма профиля -->
        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
        >
            <h2
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] text-center whitespace-nowrap"
            >
                ДАННЫЕ ПРОФИЛЯ
            </h2>

            <!-- Режим просмотра -->
            <div v-if="!isEditing" class="mt-4 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            ФИО
                        </label>
                        <div
                            class="px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            {{ user.full_name || "—" }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Телефон
                        </label>
                        <div
                            class="px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            {{ user.phone || "—" }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Email
                        </label>
                        <div
                            class="px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            {{ user.email || "—" }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Дата рождения
                        </label>
                        <div
                            class="px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            {{ formatDate(user.birth_date) || "—" }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Telegram
                        </label>
                        <div
                            class="px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            {{ user.telegram ? `@${user.telegram.replace('@', '')}` : "—" }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Адрес доставки
                        </label>
                        <div
                            class="px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        >
                            {{ user.delivery_address || "—" }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        @click="startEdit"
                        class="bg-[#C3006B] hover:bg-[#A8005A] text-white px-8 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50"
                    >
                        Редактировать
                    </button>
                </div>
            </div>

            <!-- Режим редактирования -->
            <form
                v-else
                @submit.prevent="handleSubmit"
                class="mt-4 space-y-6"
            >
                <div
                    v-if="errors.general"
                    class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-4 py-3 dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
                >
                    {{ errors.general }}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            ФИО <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.full_name"
                            type="text"
                            class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.full_name,
                            }"
                        />
                        <p
                            v-if="errors.full_name"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.full_name }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Телефон <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.phone"
                            v-maska
                            data-maska="+7 (###) ###-##-##"
                            type="tel"
                            placeholder="+7 (999) 123-45-67"
                            class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.phone,
                            }"
                        />
                        <p
                            v-if="errors.phone"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.phone }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.email,
                            }"
                        />
                        <p
                            v-if="errors.email"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.email }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Дата рождения
                        </label>
                        <input
                            v-model="form.birth_date"
                            type="date"
                            class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.birth_date,
                            }"
                        />
                        <p
                            v-if="errors.birth_date"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.birth_date }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Telegram
                        </label>
                        <input
                            v-model="form.telegram"
                            type="text"
                            placeholder="@username или username"
                            class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.telegram,
                            }"
                        />
                        <p
                            v-if="errors.telegram"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.telegram }}
                        </p>
                        <p class="mt-1 text-xs font-jost-regular text-dark-gray-400 dark:text-gray-400">
                            Укажите ваш Telegram username (с @ или без)
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Адрес доставки
                        </label>
                        <input
                            v-model="form.delivery_address"
                            type="text"
                            placeholder="Введите адрес доставки"
                            class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.delivery_address,
                            }"
                        />
                        <p
                            v-if="errors.delivery_address"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.delivery_address }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <button
                        type="button"
                        @click="cancelEdit"
                        class="px-8 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-gray-500/50 border border-dark-gray-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-700/80"
                    >
                        Отмена
                    </button>
                    <button
                        type="submit"
                        :disabled="isSaving"
                        class="bg-[#C3006B] hover:bg-[#A8005A] text-white px-8 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isSaving ? "Сохранение..." : "Сохранить" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped></style>
