<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";
import * as yup from "yup";

export default {
    name: "SetPasswordModal",
    data() {
        return {
            form: {
                new_password: "",
                new_password_confirmation: "",
            },
            errors: {},
            schema: yup.object().shape({
                new_password: yup
                    .string()
                    .required("Пароль обязателен")
                    .min(6, "Минимум 6 символов"),
                new_password_confirmation: yup
                    .string()
                    .required("Подтвердите пароль")
                    .oneOf(
                        [yup.ref("new_password")],
                        "Пароли не совпадают"
                    ),
            }),
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    methods: {
        async handleSubmit() {
            this.errors = {};
            try {
                await this.schema.validate(this.form, { abortEarly: false });
                const result = await this.authStore.setPassword(
                    this.form.new_password,
                    this.form.new_password_confirmation
                );
                if (result.success) {
                    this.form.new_password = "";
                    this.form.new_password_confirmation = "";
                } else {
                    this.errors.general = result.error;
                }
            } catch (err) {
                if (err.inner) {
                    err.inner.forEach((e) => {
                        this.errors[e.path] = e.message;
                    });
                } else {
                    this.errors.general = err.message;
                }
            }
        },
    },
};
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="set-password-title"
        >
            <div
                class="bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-600 rounded-xl max-w-md w-full p-8"
                @click.stop
            >
                <h2
                    id="set-password-title"
                    class="text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-2"
                >
                    Установите постоянный пароль
                </h2>
                <p
                    class="text-sm text-gray-600 dark:text-gray-400 mb-6"
                >
                    Вы вошли по временному паролю. Задайте свой пароль для входа в личный кабинет.
                </p>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div v-if="errors.general" class="text-sm text-red-600 dark:text-red-400">
                        {{ errors.general }}
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Новый пароль
                        </label>
                        <input
                            v-model="form.new_password"
                            type="password"
                            placeholder="Минимум 6 символов"
                            class="w-full px-4 py-3 border rounded-lg bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-[#C3006B] focus:border-transparent"
                            :class="{ 'border-red-500': errors.new_password }"
                        />
                        <p v-if="errors.new_password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.new_password }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Подтверждение пароля
                        </label>
                        <input
                            v-model="form.new_password_confirmation"
                            type="password"
                            placeholder="Повторите пароль"
                            class="w-full px-4 py-3 border rounded-lg bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-[#C3006B] focus:border-transparent"
                            :class="{ 'border-red-500': errors.new_password_confirmation }"
                        />
                        <p v-if="errors.new_password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.new_password_confirmation }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="authStore.isLoading"
                        class="w-full bg-[#C3006B] hover:bg-[#A8005A] text-white font-jost-bold py-3 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="authStore.isLoading">Сохранение...</span>
                        <span v-else>Сохранить пароль</span>
                    </button>
                </form>
            </div>
        </div>
    </Teleport>
</template>
