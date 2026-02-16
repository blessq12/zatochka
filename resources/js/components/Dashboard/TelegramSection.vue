<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../../stores/authStore.js";

export default {
    name: "TelegramSection",
    data() {
        return {
            verificationCode: "",
            isSendingCode: false,
            isVerifyingCode: false,
            codeSent: false,
            error: null,
            successMessage: null,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
        user() {
            return this.authStore.user || {};
        },
        telegramUsername() {
            const telegram = this.user.telegram || "";
            // Убираем @ если есть
            return telegram.replace(/^@/, "");
        },
        hasTelegramUsername() {
            return !!this.telegramUsername;
        },
        isTelegramVerified() {
            return this.authStore.telegramVerified || false;
        },
    },
    methods: {
        async sendVerificationCode() {
            this.isSendingCode = true;
            this.error = null;
            this.successMessage = null;
            this.codeSent = false;
            this.verificationCode = "";

            const result = await this.authStore.sendTelegramVerificationCode();

            this.isSendingCode = false;

            if (result.success) {
                this.codeSent = true;
                this.successMessage = `Код отправлен в Telegram (@${result.data?.telegramUsername || this.telegramUsername}). Действителен ${result.data?.expiresInMinutes || 5} минут.`;
            } else {
                this.error = result.error || "Ошибка отправки кода";
            }
        },
        async verifyCode() {
            if (!this.verificationCode || this.verificationCode.length !== 6) {
                this.error = "Код должен содержать 6 цифр";
                return;
            }

            this.isVerifyingCode = true;
            this.error = null;
            this.successMessage = null;

            const result = await this.authStore.verifyTelegramCode(
                this.verificationCode
            );

            this.isVerifyingCode = false;

            if (result.success) {
                this.verificationCode = "";
                this.codeSent = false;
                this.successMessage = "Telegram успешно подтвержден!";
                // Обновляем данные пользователя
                await this.authStore.checkAuth();
            } else {
                this.error = result.error || "Ошибка подтверждения кода";
            }
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500 dark:backdrop-blur-xl"
        >
            <h2
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500 text-lg sm:text-xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] text-center whitespace-nowrap"
            >
                ИНТЕГРАЦИЯ С TELEGRAM
            </h2>

            <div class="mt-4">
                <!-- Если Telegram не указан -->
                <div
                    v-if="!hasTelegramUsername"
                    class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60"
                >
                    <div
                        class="bg-yellow-50/80 backdrop-blur-lg border border-yellow-300/50 text-yellow-700 px-4 py-3 mb-4 dark:bg-yellow-900/30 dark:border-yellow-600/50 dark:text-yellow-400"
                    >
                        <p class="font-jost-medium mb-1">
                            Telegram username не указан
                        </p>
                        <p class="text-sm">
                            Для подключения Telegram необходимо указать ваш
                            Telegram username в настройках профиля.
                        </p>
                    </div>

                    <p
                        class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                    >
                        Перейдите во вкладку <strong>Профиль</strong> и
                        укажите ваш Telegram username в поле "Telegram", затем
                        вернитесь на эту вкладку.
                    </p>
                </div>

                <!-- Если Telegram указан -->
                <div
                    v-else
                    class="space-y-6"
                >
                    <!-- Информация о подключенном Telegram -->
                    <div
                        class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                            <h3
                                class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300"
                            >
                                Telegram
                            </h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="px-4 py-2 font-jost-medium text-sm sm:text-base break-all"
                                    :class="[
                                        isTelegramVerified
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
                                    ]"
                                >
                                    @{{ telegramUsername }}
                                </span>
                                <span
                                    v-if="isTelegramVerified"
                                    class="px-4 py-2 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 font-jost-medium text-sm sm:text-base whitespace-nowrap"
                                >
                                    Подтвержден
                                </span>
                            </div>
                        </div>
                        <p
                            class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 mb-4"
                        >
                            Ваш Telegram username указан в профиле.
                        </p>
                    </div>

                    <!-- Инструкция -->
                    <div
                        class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60"
                    >
                        <h3
                            class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-4"
                        >
                            Как подключить Telegram бота
                        </h3>
                        <ol
                            class="space-y-3 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 list-decimal list-inside mb-6"
                        >
                            <li>
                                Откройте приложение
                                <strong>Telegram</strong> на вашем устройстве
                            </li>
                            <li>
                                Найдите бота
                                <strong>@zatochkatsk_bot</strong> в поиске Telegram
                            </li>
                            <li>
                                Нажмите кнопку
                                <strong>«Начать»</strong> или отправьте команду
                                <strong>/start</strong>
                            </li>
                            <li>
                                После начала диалога нажмите кнопку ниже для
                                отправки кода подтверждения
                            </li>
                        </ol>

                        <!-- Сообщения об ошибках и успехе -->
                        <div
                            v-if="error"
                            class="mb-4 bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-4 py-3 dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400"
                        >
                            {{ error }}
                        </div>
                        <div
                            v-if="successMessage"
                            class="mb-4 bg-green-50/80 backdrop-blur-lg border border-green-300/50 text-green-700 px-4 py-3 dark:bg-green-900/30 dark:border-green-600/50 dark:text-green-400"
                        >
                            {{ successMessage }}
                        </div>

                        <!-- Отправка кода -->
                        <div v-if="!codeSent" class="mb-6">
                            <button
                                @click="sendVerificationCode"
                                :disabled="isSendingCode || isTelegramVerified"
                                class="bg-[#C3006B] hover:bg-[#A8005A] text-white px-6 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{
                                    isSendingCode
                                        ? "Отправка..."
                                        : isTelegramVerified
                                        ? "Уже подтвержден"
                                        : "Отправить код подтверждения"
                                }}
                            </button>
                        </div>

                        <!-- Ввод кода -->
                        <div v-if="codeSent && !isTelegramVerified" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm sm:text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 mb-2"
                                >
                                    Введите код подтверждения
                                </label>
                                <input
                                    v-model="verificationCode"
                                    type="text"
                                    maxlength="6"
                                    placeholder="000000"
                                    pattern="[0-9]*"
                                    inputmode="numeric"
                                    class="w-full px-4 py-3 bg-white/60 backdrop-blur-md border border-white/20 shadow-lg focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 focus:border-[#C3006B]/50 transition-all duration-300 text-dark-gray-500 dark:text-gray-200 dark:bg-gray-800/60 dark:border-gray-700/20 text-center text-2xl font-jost-bold tracking-widest"
                                    @keyup.enter="verifyCode"
                                    @input="verificationCode = verificationCode.replace(/[^0-9]/g, '')"
                                />
                                <p
                                    class="mt-2 text-xs font-jost-regular text-dark-gray-400 dark:text-gray-400"
                                >
                                    Введите 6-значный код из Telegram
                                </p>
                            </div>
                            <div class="flex gap-4">
                                <button
                                    @click="codeSent = false; verificationCode = ''; error = null; successMessage = null"
                                    class="px-6 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-gray-500/50 border border-dark-gray-500/30 dark:border-dark-gray-200/90 text-dark-gray-500 dark:text-gray-200 hover:bg-gray-100/80 dark:hover:bg-gray-700/80"
                                >
                                    Отменить
                                </button>
                                <button
                                    @click="verifyCode"
                                    :disabled="isVerifyingCode || verificationCode.length !== 6"
                                    class="bg-[#C3006B] hover:bg-[#A8005A] text-white px-6 py-3 font-jost-bold text-base sm:text-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform focus:outline-none focus:ring-2 focus:ring-[#C3006B]/50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{
                                        isVerifyingCode
                                            ? "Подтверждение..."
                                            : "Подтвердить"
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Полезная информация -->
                    <div
                        class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60"
                    >
                        <h3
                            class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-4"
                        >
                            Что вы получите
                        </h3>
                        <ul
                            class="space-y-2 text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300 list-disc list-inside"
                        >
                            <li>Уведомления о статусе заказов</li>
                            <li>Информацию о готовности заказа</li>
                            <li>Напоминания и важные сообщения</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
