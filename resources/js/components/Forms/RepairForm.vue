<script>
import { mapStores } from "pinia";
import * as yup from "yup";
import { useOrderStore } from "../../stores/orderStore.js";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "RepairForm",
    data() {
        return {
            form: {
                device_name: "",
                equipment_type: "",
                problem_description: "",
                urgency_type: "standard", // radio кнопка - один выбор
                needs_delivery: true,
                name: "",
                phone: "",
                comment: "",
                delivery_address: "",
                delivery_agreement: false,
                privacy_agreement: false,
            },
            errors: {},
            equipmentTypes: [
                { value: "clipper", label: "Машинка для стрижки" },
                { value: "trimmer", label: "Триммер" },
                { value: "shaver", label: "Бритва" },
                { value: "dryer", label: "Фен" },
                { value: "other", label: "Другое" },
            ],
            schema: yup.object().shape({
                device_name: yup
                    .string()
                    .required("Наименование аппарата обязательно")
                    .min(2, "Минимум 2 символа"),
                equipment_type: yup
                    .string()
                    .required("Выберите тип оборудования"),
                problem_description: yup
                    .string()
                    .required("Описание проблемы обязательно")
                    .min(10, "Минимум 10 символов"),
                urgency_type: yup
                    .string()
                    .required("Выберите срочность ремонта"),
                name: yup
                    .string()
                    .required("Имя обязательно")
                    .min(2, "Минимум 2 символа"),
                phone: yup
                    .string()
                    .required("Телефон обязателен")
                    .min(18, "Номер телефона должен быть 18 символов")
                    .max(18, "Номер телефона должен быть 18 символов"),
                delivery_agreement: yup
                    .boolean()
                    .oneOf([true], "Необходимо согласие с условиями доставки"),
                privacy_agreement: yup
                    .boolean()
                    .oneOf([true], "Необходимо согласие на обработку персональных данных"),
            }),
        };
    },
    computed: {
        ...mapStores(useOrderStore, useAuthStore),
    },
    async mounted() {
        // Проверяем авторизацию и загружаем данные пользователя
        if (this.authStore.isAuthenticated && !this.authStore.user) {
            await this.authStore.checkAuth();
        }
        
        // Автозаполняем форму данными пользователя, если он авторизован
        if (this.authStore.isAuthenticated && this.authStore.user) {
            this.fillUserData();
        }
    },
    methods: {
        fillUserData() {
            const user = this.authStore.user;
            if (!user) return;
            
            // Заполняем имя
            if (user.full_name) {
                this.form.name = user.full_name;
            }
            
            // Заполняем телефон (формат +7 (###) ###-##-##)
            if (user.phone) {
                // Если телефон уже в нужном формате, используем как есть
                if (user.phone.match(/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/)) {
                    this.form.phone = user.phone;
                } else {
                    // Убираем все нецифровые символы, кроме +
                    let cleanPhone = user.phone.replace(/[^\d+]/g, '');
                    // Если нет +, добавляем +7
                    if (!cleanPhone.startsWith('+')) {
                        // Убираем ведущую 7 или 8, добавляем +7
                        cleanPhone = cleanPhone.replace(/^[78]/, '');
                        cleanPhone = '+7' + cleanPhone;
                    } else if (cleanPhone.startsWith('+')) {
                        // Если есть +, но не 7, заменяем
                        cleanPhone = cleanPhone.replace(/^\+[^7]/, '+7');
                    }
                    
                    // Форматируем в +7 (###) ###-##-## (10 цифр после +7)
                    const digits = cleanPhone.replace(/\+7/, '').replace(/\D/g, '');
                    if (digits.length === 10) {
                        const match = digits.match(/^(\d{3})(\d{3})(\d{2})(\d{2})$/);
                        if (match) {
                            this.form.phone = `+7 (${match[1]}) ${match[2]}-${match[3]}-${match[4]}`;
                        } else {
                            this.form.phone = user.phone;
                        }
                    } else {
                        this.form.phone = user.phone;
                    }
                }
            }
            
            // Заполняем адрес доставки, если указан
            if (user.delivery_address) {
                this.form.delivery_address = user.delivery_address;
            }
        },
        async submitForm() {
            this.errors = {};
            try {
                await this.schema.validate(this.form, {
                    abortEarly: false,
                });

                const result = await this.orderStore.createOrder(
                    this.form,
                    "repair"
                );

                if (result.success) {
                    // Сброс формы после успешной отправки
                    this.form = {
                        device_name: "",
                        equipment_type: "",
                        problem_description: "",
                        urgency_type: "standard",
                        needs_delivery: true,
                        name: "",
                        phone: "",
                        comment: "",
                        delivery_address: "",
                        delivery_agreement: false,
                        privacy_agreement: false,
                    };
                    
                    // Повторно заполняем данные пользователя, если он авторизован
                    if (this.authStore.isAuthenticated && this.authStore.user) {
                        this.fillUserData();
                    }
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
            }
        },
    },
};
</script>

<template>
    <div>
        <!-- Секция формы -->
        <section class="bg-white dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20">
            <div class="max-w-3xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20">
                <!-- Заголовок -->
                <div class="mb-8 sm:mb-12">
                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-jost-bold text-dark-blue-500 dark:text-white mb-4"
                    >
                        Оставить заявку на ремонт инструментов
                    </h1>
                    <div class="h-px bg-[#C3006B] dark:bg-[#C3006B]"></div>
                </div>
                <!-- Форма -->
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Общая ошибка -->
                    <div
                        v-if="errors.general"
                        class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
                    >
                        {{ errors.general }}
                    </div>

                    <!-- Наименование аппарата -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Наименование аппарата <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.device_name"
                            type="text"
                            placeholder="Например, машинка для стрижки"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.device_name,
                            }"
                        />
                        <p
                            v-if="errors.device_name"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.device_name }}
                        </p>
                    </div>

                    <!-- Тип оборудования -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Тип оборудования <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.equipment_type"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20 appearance-none bg-no-repeat bg-right pr-12"
                            :class="{
                                'border-red-500': errors.equipment_type,
                            }"
                            style="
                                background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23333%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e');
                                background-position: right 1rem center;
                                background-size: 1.5em 1.5em;
                            "
                        >
                            <option value="" disabled>
                                Выберите тип оборудования
                            </option>
                            <option
                                v-for="type in equipmentTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                        <p
                            v-if="errors.equipment_type"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.equipment_type }}
                        </p>
                    </div>

                    <!-- Описание проблемы -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Описание проблемы <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.problem_description"
                            rows="4"
                            placeholder="Опишите, что происходит с оборудованием"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20 resize-none"
                            :class="{
                                'border-red-500': errors.problem_description,
                            }"
                        ></textarea>
                        <p
                            v-if="errors.problem_description"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.problem_description }}
                        </p>
                    </div>

                    <!-- Срочность ремонта -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-3"
                        >
                            Срочность ремонта <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-row gap-6">
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="form.urgency_type"
                                    type="radio"
                                    value="standard"
                                    id="urgency_standard"
                                    class="w-5 h-5 border-gray-300 text-[#C3006B] focus:ring-[#C3006B]"
                                />
                                <label
                                    for="urgency_standard"
                                    class="text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Обычный ремонт, 1-5 дней
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="form.urgency_type"
                                    type="radio"
                                    value="urgent"
                                    id="urgency_urgent"
                                    class="w-5 h-5 border-gray-300 text-[#C3006B] focus:ring-[#C3006B]"
                                />
                                <label
                                    for="urgency_urgent"
                                    class="text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                >
                                    Срочный ремонт 24 часа (+50%)
                                </label>
                            </div>
                        </div>
                        <p
                            v-if="errors.urgency_type"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.urgency_type }}
                        </p>
                    </div>

                    <!-- Нужна доставка -->
                    <div class="flex items-center gap-3">
                        <input
                            v-model="form.needs_delivery"
                            type="checkbox"
                            id="needs_delivery"
                            class="w-5 h-5 border-gray-300 text-[#C3006B] focus:ring-[#C3006B]"
                        />
                        <label
                            for="needs_delivery"
                            class="text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200"
                        >
                            Нужна доставка
                        </label>
                    </div>

                    <!-- Адрес доставки (показывается только если нужна доставка) -->
                    <div v-if="form.needs_delivery">
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Адрес доставки
                        </label>
                        <input
                            v-model="form.delivery_address"
                            type="text"
                            placeholder="Введите адрес доставки"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        />
                    </div>

                    <!-- Ваше имя -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Ваше имя <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Введите ваше имя"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.name,
                            }"
                        />
                        <p
                            v-if="errors.name"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Телефон -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Телефон <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.phone"
                            v-maska
                            data-maska="+7 (###) ###-##-##"
                            type="tel"
                            placeholder="+7 (___) ___-__-__"
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                            :class="{
                                'border-red-500': errors.phone,
                            }"
                        />
                        <p
                            v-if="errors.phone"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ errors.phone }}
                        </p>
                    </div>

                    <!-- Комментарий -->
                    <div>
                        <label
                            class="block text-base sm:text-lg font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                        >
                            Комментарий
                        </label>
                        <input
                            v-model="form.comment"
                            type="text"
                            placeholder=""
                            class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20"
                        />
                    </div>

                    <!-- Чекбоксы согласий -->
                    <div class="space-y-4 pt-4">
                        <div class="flex items-start gap-3">
                            <input
                                v-model="form.delivery_agreement"
                                type="checkbox"
                                id="delivery_agreement"
                                class="w-5 h-5 border-gray-300 text-[#C3006B] focus:ring-[#C3006B] mt-1 flex-shrink-0"
                                :class="{
                                    'border-red-500': errors.delivery_agreement,
                                }"
                            />
                            <label
                                for="delivery_agreement"
                                class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200"
                            >
                                Я ознакомлен с
                                <span class="underline"
                                    >условиями доставки</span
                                >
                                <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <p
                            v-if="errors.delivery_agreement"
                            class="text-sm text-red-600 dark:text-red-400 ml-8"
                        >
                            {{ errors.delivery_agreement }}
                        </p>

                        <div class="flex items-start gap-3">
                            <input
                                v-model="form.privacy_agreement"
                                type="checkbox"
                                id="privacy_agreement"
                                class="w-5 h-5 border-gray-300 text-[#C3006B] focus:ring-[#C3006B] mt-1 flex-shrink-0"
                                :class="{
                                    'border-red-500': errors.privacy_agreement,
                                }"
                            />
                            <label
                                for="privacy_agreement"
                                class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-200"
                            >
                                Даю согласие на
                                <span class="underline"
                                    >обработку персональных данных</span
                                >
                                <span class="text-red-500">*</span>
                            </label>
                        </div>
                        <p
                            v-if="errors.privacy_agreement"
                            class="text-sm text-red-600 dark:text-red-400 ml-8"
                        >
                            {{ errors.privacy_agreement }}
                        </p>
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="pt-6">
                        <button
                            type="submit"
                            :disabled="orderStore.createOrderLoading"
                            class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 text-white border-2 border-white px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        >
                            <span v-if="orderStore.createOrderLoading">Отправка...</span>
                            <span v-else>ЗАКАЗАТЬ РЕМОНТ</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Футер с контактами -->
        <section
            class="bg-dark-blue-500 dark:bg-dark-blue-500 py-12 sm:py-16 lg:py-20"
        >
            <div
                class="max-w-3xl mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 text-center"
            >
                <!-- Декоративные иконки -->
                <div
                    class="flex justify-center items-center gap-6 mb-8 sm:mb-12"
                >
                    <svg
                        width="98"
                        height="33"
                        viewBox="0 0 98 33"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M75.7866 25.0391C76.1404 22.0891 81.5193 15.1516 84.1059 11.8191C84.8709 10.8342 85.4733 10.0548 85.7889 9.60062C85.8701 9.48109 85.9562 9.36156 86.0423 9.23725C86.1283 9.36156 86.2144 9.48587 86.2957 9.60062C86.6065 10.0548 87.2136 10.8342 87.9786 11.8191C90.5652 15.1516 95.9393 22.0891 96.2979 25.0391C96.776 29.0074 94.7679 31.661 94.7488 31.6897L96.0302 32.689C96.1306 32.5599 98.4733 29.5047 97.9139 24.843C97.5362 21.7114 93.2523 15.9644 89.2648 10.8246C88.5141 9.8588 87.926 9.09381 87.6392 8.67785C87.5531 8.55354 87.4623 8.42445 87.3715 8.29057C87.2567 8.12801 87.1371 7.95589 87.0224 7.78377C87.7874 6.56457 88.5285 5.11109 88.7437 3.49026C88.8823 2.45275 88.4855 1.68776 88.2512 1.00405L86.3531 0C86.7786 1.22398 87.1993 2.8209 87.1371 3.27511C86.9937 4.33653 86.5682 5.33102 86.0566 6.24423C85.5403 5.33102 85.1147 4.33653 84.9761 3.27511C84.9187 2.8209 85.3347 1.22398 85.7602 0L83.9577 1.01839C83.7425 1.64473 83.3457 2.07982 83.3457 3.20817C83.3457 3.30858 83.3505 3.4042 83.36 3.49026C83.5752 5.11109 84.3115 6.55979 85.0813 7.78377C84.9617 7.95589 84.8422 8.12801 84.7322 8.29057C84.6414 8.42445 84.5505 8.55354 84.4645 8.67785C84.1776 9.09381 83.5895 9.8588 82.8388 10.8246C78.8513 15.9644 74.5674 21.7114 74.1945 24.843C74.1323 25.3403 74.1084 25.8184 74.1084 26.2774C74.1084 30.1215 75.9875 32.5694 76.0783 32.6842L77.3596 31.6849C77.3405 31.6562 75.3324 29.0027 75.8105 25.0343L75.7866 25.0391Z"
                            fill="#C3006B"
                        />
                        <path
                            d="M4.38435 30.4594C5.97648 30.4594 7.37259 29.6036 8.14236 28.3318C8.91213 29.6036 10.3082 30.4594 11.9004 30.4594C14.3149 30.4594 16.2847 28.4944 16.2847 26.0751C16.2847 23.804 14.5491 21.9298 12.3307 21.7147L8.82129 13.1659L10.9967 7.86356C11.231 7.32328 11.427 6.76867 11.5992 6.20927L10.5808 5.25781C10.3752 5.97499 10.1409 6.68261 9.84446 7.37588L8.14714 11.5116L6.44504 7.36631C6.1486 6.67782 5.91432 5.97021 5.70873 5.25781L4.69034 6.20927C4.86246 6.76389 5.05849 7.31372 5.28799 7.84921L7.46821 13.1611L3.95882 21.7099C1.73557 21.9346 0 23.8088 0 26.0799C0 28.4944 1.96507 30.4642 4.38435 30.4642V30.4594ZM15.0273 26.0751C15.0273 27.8011 13.6216 29.2068 11.8956 29.2068C10.1696 29.2068 8.76391 27.8011 8.76391 26.0751C8.76391 24.3491 10.1696 22.9434 11.8956 22.9434C13.6216 22.9434 15.0273 24.3491 15.0273 26.0751ZM8.14236 14.8154L11.0015 21.7864C9.79187 22.0398 8.76391 22.7952 8.14236 23.8232C7.52558 22.7952 6.49285 22.0398 5.28321 21.7864L8.14236 14.8154ZM4.38435 22.9434C6.11035 22.9434 7.51602 24.3491 7.51602 26.0751C7.51602 27.8011 6.11035 29.2068 4.38435 29.2068C2.65834 29.2068 1.25267 27.8011 1.25267 26.0751C1.25267 24.3491 2.65834 22.9434 4.38435 22.9434Z"
                            fill="#C3006B"
                        />
                        <path
                            d="M60.8165 10.5076V13.6584H67.6393C67.9309 13.6584 68.1652 13.8926 68.1652 14.1843C68.1652 14.4759 67.9309 14.7102 67.6393 14.7102H60.8165V17.861H67.6393C67.9309 17.861 68.1652 18.0953 68.1652 18.3869C68.1652 18.6786 67.9309 18.9129 67.6393 18.9129H60.8165V22.0637H67.6393C67.9309 22.0637 68.1652 22.298 68.1652 22.5896C68.1652 22.8813 67.9309 23.1155 67.6393 23.1155H60.8165V29.9383C60.8165 30.2299 60.5822 30.4642 60.2906 30.4642C59.9989 30.4642 59.7646 30.2299 59.7646 29.9383V8.93454C59.7646 6.90732 61.4142 5.25781 63.4414 5.25781H67.6393C67.9309 5.25781 68.1652 5.49209 68.1652 5.78374C68.1652 6.0754 67.9309 6.30967 67.6393 6.30967H63.4414C61.9927 6.30967 60.8165 7.48584 60.8165 8.93454V9.46047H67.6393C67.9309 9.46047 68.1652 9.69475 68.1652 9.9864C68.1652 10.2781 67.9309 10.5123 67.6393 10.5123H60.8165V10.5076Z"
                            fill="#C3006B"
                        />
                        <path
                            d="M24.7277 28.9486C24.4169 28.8482 24.0344 28.7047 23.6472 28.547C23.6472 28.131 23.6615 27.7246 23.6902 27.3995C23.7189 27.0744 23.8528 26.754 24.0679 26.4958C26.3964 23.7132 34.1658 11.8654 39.7167 7.84443C41.1081 6.84517 46.4773 6.55351 48.428 6.52961L46.1092 5.25781C43.7377 5.40603 40.257 5.77896 38.9231 6.73998C32.6741 11.2391 23.2599 25.334 22.6527 26.0703L22.5905 26.1468L22.5523 26.2329C22.2224 26.9883 22.2798 28.8816 22.3037 29.4458C22.8152 29.6849 24.546 30.4594 25.3684 30.4499H25.464L25.5549 30.4212C26.4681 30.1391 43.0444 26.7875 49.5994 22.7426C51.0003 21.882 52.6833 18.8077 53.7304 16.6753L53.4483 14.0456C52.6737 15.8386 50.3453 20.6867 48.887 21.5856C43.0397 25.1714 29.1694 27.8728 25.7031 28.9534C25.3827 29.0538 25.0433 29.0538 24.7229 28.9534L24.7277 28.9486Z"
                            fill="#C3006B"
                        />
                    </svg>
                </div>

                <!-- Текст призыва -->
                <p
                    class="text-xl sm:text-2xl font-jost-regular text-white mb-8 sm:mb-12"
                >
                    Свяжитесь с нами для заказа доставки ваших инструментов
                </p>

                <!-- Кнопки контактов -->
                <div
                    class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center mb-8"
                >
                    <button
                        class="bg-white hover:bg-gray-100 text-dark-blue-500 border-2 border-dark-blue-500 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform"
                    >
                        ПОЗВОНИТЬ
                    </button>
                    <button
                        class="bg-white hover:bg-gray-100 text-dark-blue-500 border-2 border-dark-blue-500 px-10 py-5 font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform"
                    >
                        НАПИСАТЬ
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped></style>
