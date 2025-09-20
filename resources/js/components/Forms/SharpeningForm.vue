<script>
import * as yup from "yup";
import { useAuthStore } from "../../stores/authStore.js";
import { useOrderStore } from "../../stores/orderStore.js";

export default {
    name: "SharpeningForm",
    data() {
        return {
            form: {
                name: "",
                phone: "",
                comment: "",
                agreement: true,
                privacy_agreement: true,
                tools_count: "",
                tool_type: "",
                needs_delivery: false,
                delivery_address: "",
            },
            loading: false,
            errors: {},
        };
    },
    computed: {
        authStore() {
            return useAuthStore();
        },
        orderStore() {
            return useOrderStore();
        },
    },
    watch: {
        "authStore.user": {
            handler() {
                this.fillUserData();
            },
            immediate: true,
        },
    },
    mounted() {
        this.fillUserData();
    },
    methods: {
        fillUserData() {
            if (this.authStore.isAuthenticated && this.authStore.user) {
                this.form.name = this.authStore.user.full_name || "";
                this.form.phone = this.authStore.user.phone || "";
            }
        },

        async validateForm() {
            const schema = yup.object({
                name: yup
                    .string()
                    .required("Укажите ваше имя")
                    .min(2, "Имя должно содержать минимум 2 символа"),
                phone: yup
                    .string()
                    .required("Укажите номер телефона")
                    .matches(
                        /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/,
                        "Введите корректный номер телефона"
                    ),
                tools_count: yup
                    .number()
                    .typeError("Укажите количество инструментов")
                    .required("Укажите количество инструментов")
                    .min(1, "Минимум 1 инструмент")
                    .integer("Количество должно быть целым числом"),
                tool_type: yup
                    .string()
                    .required("Выберите тип инструментов")
                    .oneOf(
                        ["manicure", "hair", "grooming", "barber", "other"],
                        "Выберите корректный тип инструментов"
                    ),
                delivery_address: yup.string().when("needs_delivery", {
                    is: true,
                    then: (schema) =>
                        schema
                            .required("Укажите адрес доставки")
                            .min(
                                10,
                                "Адрес должен содержать минимум 10 символов"
                            ),
                    otherwise: (schema) => schema.nullable(),
                }),
                agreement: yup
                    .boolean()
                    .oneOf([true], "Необходимо согласие с условиями доставки"),
                privacy_agreement: yup
                    .boolean()
                    .oneOf([true], "Необходимо согласие на обработку данных"),
            });

            try {
                this.errors = {};
                await schema.validate(this.form, { abortEarly: false });
                return { valid: true };
            } catch (e) {
                const fieldErrors = {};
                if (e.inner?.length) {
                    e.inner.forEach((err) => {
                        if (!fieldErrors[err.path]) {
                            fieldErrors[err.path] = err.message;
                        }
                    });
                } else if (e.path) {
                    fieldErrors[e.path] = e.message;
                }
                this.errors = fieldErrors;
                return { valid: false };
            }
        },

        async submitForm() {
            this.loading = true;
            this.error = null;

            try {
                // Валидация
                const { valid } = await this.validateForm();
                if (!valid) {
                    this.loading = false;
                    return;
                }

                // Отправка заказа через стор
                const result = await this.orderStore.createOrder(
                    this.form,
                    "sharpening"
                );

                this.loading = false;
            } catch (err) {
                this.loading = false;
            }
        },
    },
};
</script>

<template>
    <div class="space-y-8">
        <form @submit.prevent="submitForm" class="space-y-8">
            <!-- Количество инструментов -->
            <div class="space-y-3">
                <label
                    class="block text-lg font-jost-medium text-gray-700 dark:text-white mb-3"
                >
                    Количество инструментов
                </label>
                <div class="relative">
                    <input
                        type="number"
                        min="1"
                        class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 dark:bg-gray-800/60 dark:border-gray-600/30"
                        :class="{
                            'border-red-500':
                                errors.tools_count || !form.tools_count,
                        }"
                        v-model="form.tools_count"
                        placeholder="Введите количество"
                    />
                    <p
                        v-if="errors.tools_count"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ errors.tools_count }}
                    </p>
                </div>
            </div>

            <!-- Тип инструментов -->
            <div class="space-y-3">
                <label
                    class="block text-lg font-jost-medium text-gray-700 dark:text-white mb-3"
                >
                    Тип инструментов
                </label>
                <div class="relative">
                    <select
                        class="w-full px-6 py-4 pr-10 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 appearance-none text-gray-900 dark:text-white dark:bg-gray-800/60 dark:border-gray-600/30"
                        :class="{
                            'border-red-500':
                                errors.tool_type || !form.tool_type,
                        }"
                        v-model="form.tool_type"
                    >
                        <option value="">Выберите тип инструментов</option>
                        <option value="manicure">Маникюрные</option>
                        <option value="hair">Парикмахерские</option>
                        <option value="grooming">Груминг</option>
                        <option value="barber">Барберские</option>
                        <option value="other">Другое</option>
                    </select>
                    <i
                        class="mdi mdi-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none text-xl"
                    ></i>
                    <p
                        v-if="errors.tool_type"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ errors.tool_type }}
                    </p>
                </div>
            </div>

            <!-- Нужна ли доставка -->
            <div class="space-y-3">
                <div
                    class="flex items-center gap-3 p-4 bg-white/60 backdrop-blur-md rounded-2xl border border-white/20 shadow-lg dark:bg-gray-800/60 dark:border-gray-600/30"
                >
                    <input
                        type="checkbox"
                        class="w-6 h-6 text-blue-500 accent-blue-500"
                        v-model="form.needs_delivery"
                    />
                    <label class="dark:text-white text-lg font-jost-medium"
                        >Нужна доставка</label
                    >
                </div>
            </div>

            <!-- Адрес доставки (если нужна) -->
            <div v-if="form.needs_delivery" class="space-y-3">
                <label
                    class="block text-lg font-jost-medium text-gray-700 dark:text-white mb-3"
                >
                    Адрес доставки
                </label>
                <div class="relative">
                    <textarea
                        class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 resize-y min-h-[120px] text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 dark:bg-gray-800/60 dark:border-gray-600/30"
                        :class="{
                            'border-red-500':
                                errors.delivery_address ||
                                (form.needs_delivery && !form.delivery_address),
                        }"
                        rows="3"
                        placeholder="Укажите полный адрес для доставки"
                        v-model="form.delivery_address"
                    ></textarea>
                    <p
                        v-if="errors.delivery_address"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ errors.delivery_address }}
                    </p>
                </div>
            </div>

            <!-- Имя и телефон -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <label
                        class="block text-lg font-jost-medium text-gray-700 dark:text-white mb-3"
                    >
                        Ваше имя
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 dark:bg-gray-800/60 dark:border-gray-600/30"
                            :class="{
                                'border-red-500': errors.name || !form.name,
                            }"
                            v-model="form.name"
                            placeholder="Введите ваше имя"
                        />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-2">
                            {{ errors.name }}
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label
                        class="block text-lg font-jost-medium text-gray-700 dark:text-white mb-3"
                    >
                        Телефон
                    </label>
                    <div class="relative">
                        <input
                            type="tel"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 dark:bg-gray-800/60 dark:border-gray-600/30"
                            :class="{
                                'border-red-500': errors.phone || !form.phone,
                            }"
                            v-model="form.phone"
                            v-maska
                            data-maska="+7 (###) ###-##-##"
                            placeholder="+7 (___) ___-__-__"
                        />
                        <p
                            v-if="errors.phone"
                            class="text-red-500 text-sm mt-2"
                        >
                            {{ errors.phone }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Комментарий -->
            <div class="space-y-3">
                <label
                    class="block text-lg font-jost-medium text-gray-700 dark:text-white mb-3"
                >
                    Комментарий
                </label>
                <div class="relative">
                    <textarea
                        class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 resize-y min-h-[120px] text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 dark:bg-gray-800/60 dark:border-gray-600/30"
                        rows="4"
                        placeholder="Дополнительная информация о заказе"
                        v-model="form.comment"
                    ></textarea>
                </div>
            </div>

            <!-- Согласия -->
            <div class="space-y-4">
                <div
                    class="flex items-center gap-3 p-4 bg-white/60 backdrop-blur-md rounded-2xl border border-white/20 shadow-lg dark:bg-gray-800/60 dark:border-gray-600/30"
                >
                    <input
                        type="checkbox"
                        class="w-6 h-6 text-blue-500 accent-blue-500"
                        v-model="form.agreement"
                        required
                    />
                    <label class="dark:text-white text-lg font-jost-medium">
                        Я ознакомлен с
                        <a
                            href="/delivery"
                            target="_blank"
                            class="text-blue-500 hover:underline font-jost-bold"
                        >
                            условиями доставки
                        </a>
                    </label>
                </div>

                <div
                    class="flex items-center gap-3 p-4 bg-white/60 backdrop-blur-md rounded-2xl border border-white/20 shadow-lg dark:bg-gray-800/60 dark:border-gray-600/30"
                >
                    <input
                        type="checkbox"
                        class="w-6 h-6 text-blue-500 accent-blue-500"
                        v-model="form.privacy_agreement"
                        required
                    />
                    <label class="dark:text-white text-lg font-jost-medium">
                        Даю согласие на
                        <a
                            href="/privacy-policy"
                            target="_blank"
                            class="text-blue-500 hover:underline font-jost-bold"
                        >
                            обработку персональных данных
                        </a>
                    </label>
                </div>
            </div>

            <!-- Кнопка отправки -->
            <button
                type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-dark-blue-500 text-white font-jost-bold py-5 px-10 rounded-2xl shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-500 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center text-xl"
                :disabled="loading"
            >
                {{ loading ? "Отправляем..." : "Заказать заточку" }}
            </button>
        </form>
    </div>
</template>

<style scoped>
input,
select,
textarea {
    transition: all 0.3s ease;
}

input[type="checkbox"]:checked {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>
