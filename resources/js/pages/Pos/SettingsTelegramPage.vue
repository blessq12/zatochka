<template>
    <div class="pos-page-content">
        <div class="telegram-section">
            <h1 class="section-title">Интеграция с Telegram</h1>

            <!-- Если Telegram не указан -->
            <div v-if="!hasTelegramUsername" class="info-box warning">
                <h3 class="info-title">Telegram username не указан</h3>
                <p class="info-text">
                    Для подключения Telegram необходимо указать ваш
                    Telegram username в настройках профиля.
                </p>
                <router-link :to="{ name: 'pos.settings.profile' }" class="btn-link">
                    Перейти в профиль
                </router-link>
            </div>

            <!-- Если Telegram указан -->
            <div v-else class="content-wrapper">
                <!-- Информация о подключенном Telegram -->
                <div class="info-box">
                    <div class="info-header">
                        <h3 class="info-title">Telegram</h3>
                        <span class="badge telegram-badge">
                            @{{ telegramUsername }}
                        </span>
                        <span v-if="isTelegramVerified" class="badge verified-badge">
                            Подтвержден
                        </span>
                    </div>
                    <p class="info-text">
                        Ваш Telegram username указан в профиле.
                    </p>
                </div>

                <!-- Инструкция -->
                <div class="info-box instructions-box">
                    <h3 class="info-title">Как подключить Telegram бота</h3>
                    <ol class="instructions-list">
                        <li>
                            Откройте приложение
                            <strong>Telegram</strong> на вашем устройстве
                        </li>
                        <li>
                            Найдите бота
                            <strong>@zatochka_bot</strong> в поиске Telegram
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
                    <div v-if="error" class="alert alert-error">
                        {{ error }}
                    </div>
                    <div v-if="successMessage" class="alert alert-success">
                        {{ successMessage }}
                    </div>

                    <!-- Отправка кода -->
                    <div v-if="!codeSent" class="action-section">
                        <button
                            @click="sendVerificationCode"
                            :disabled="isSendingCode || isTelegramVerified"
                            class="btn-primary btn-send-code"
                        >
                            <span v-if="isSendingCode">Отправка...</span>
                            <span v-else-if="isTelegramVerified">Уже подтвержден</span>
                            <span v-else>Отправить код подтверждения</span>
                        </button>
                    </div>

                    <!-- Ввод кода -->
                    <div v-if="codeSent && !isTelegramVerified" class="action-section">
                        <div class="code-input-wrapper">
                            <label class="form-label">
                                Введите код подтверждения
                            </label>
                            <input
                                v-model="verificationCode"
                                type="text"
                                maxlength="6"
                                placeholder="000000"
                                pattern="[0-9]*"
                                inputmode="numeric"
                                class="code-input"
                                @keyup.enter="verifyCode"
                                @input="verificationCode = verificationCode.replace(/[^0-9]/g, '')"
                            />
                            <p class="code-hint">
                                Введите 6-значный код из Telegram
                            </p>
                        </div>
                        <div class="code-actions">
                            <button
                                @click="cancelVerification"
                                class="btn-secondary"
                            >
                                Отменить
                            </button>
                            <button
                                @click="verifyCode"
                                :disabled="isVerifyingCode || verificationCode.length !== 6"
                                class="btn-primary"
                            >
                                <span v-if="isVerifyingCode">Подтверждение...</span>
                                <span v-else>Подтвердить</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Полезная информация -->
                <div class="info-box benefits-box">
                    <h3 class="info-title">Что вы получите</h3>
                    <ul class="benefits-list">
                        <li>Уведомления о статусе заказов</li>
                        <li>Информацию о готовности заказа</li>
                        <li>Напоминания и важные сообщения</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import axios from "axios";

export default {
    name: "SettingsTelegramPage",
    setup() {
        const route = useRoute();
        const posStore = usePosStore();
        const { setNavigationItems, reset } = useHeaderNavigation();

        const verificationCode = ref("");
        const isSendingCode = ref(false);
        const isVerifyingCode = ref(false);
        const codeSent = ref(false);
        const error = ref(null);
        const successMessage = ref(null);

        const user = computed(() => posStore.user);
        const telegramUsername = computed(() => {
            const telegram = user.value?.telegram_username || "";
            return telegram.replace(/^@/, "");
        });
        const hasTelegramUsername = computed(() => !!telegramUsername.value);
        const isTelegramVerified = computed(() => {
            return user.value?.telegram_verified_at !== null &&
                   user.value?.telegram_verified_at !== undefined;
        });

        const sendVerificationCode = async () => {
            isSendingCode.value = true;
            error.value = null;
            successMessage.value = null;
            codeSent.value = false;
            verificationCode.value = "";

            try {
                const response = await axios.post(
                    "/api/pos/telegram/send-verification-code",
                    {},
                    { headers: { Authorization: `Bearer ${posStore.token}` } }
                );

                if (response.data.success) {
                    codeSent.value = true;
                    successMessage.value = `Код отправлен в Telegram (@${response.data.telegram_username || telegramUsername.value}). Действителен ${response.data.expires_in_minutes || 5} минут.`;
                } else {
                    error.value = response.data.message || "Ошибка отправки кода";
                }
            } catch (err) {
                error.value = err.response?.data?.message || "Ошибка отправки кода подтверждения";
            } finally {
                isSendingCode.value = false;
            }
        };

        const verifyCode = async () => {
            if (!verificationCode.value || verificationCode.value.length !== 6) {
                error.value = "Код должен содержать 6 цифр";
                return;
            }

            isVerifyingCode.value = true;
            error.value = null;
            successMessage.value = null;

            try {
                const response = await axios.post(
                    "/api/pos/telegram/verify-code",
                    { code: verificationCode.value },
                    { headers: { Authorization: `Bearer ${posStore.token}` } }
                );

                if (response.data.success) {
                    verificationCode.value = "";
                    codeSent.value = false;
                    successMessage.value = "Telegram успешно подтвержден!";
                    // Обновляем данные пользователя
                    await posStore.getMe();
                } else {
                    error.value = response.data.message || "Ошибка подтверждения кода";
                }
            } catch (err) {
                error.value = err.response?.data?.message || "Ошибка подтверждения кода";
            } finally {
                isVerifyingCode.value = false;
            }
        };

        const cancelVerification = () => {
            codeSent.value = false;
            verificationCode.value = "";
            error.value = null;
            successMessage.value = null;
        };

        // Регистрация элементов навигации в Header
        onMounted(() => {
            setNavigationItems([
                {
                    name: "profile",
                    label: "Профиль",
                    to: { name: "pos.settings.profile" },
                    active: route.name === "pos.settings.profile",
                },
                {
                    name: "telegram",
                    label: "Telegram",
                    to: { name: "pos.settings.telegram" },
                    active: route.name === "pos.settings.telegram",
                },
            ]);
        });

        onUnmounted(() => {
            reset();
        });

        return {
            user,
            telegramUsername,
            hasTelegramUsername,
            isTelegramVerified,
            verificationCode,
            isSendingCode,
            isVerifyingCode,
            codeSent,
            error,
            successMessage,
            sendVerificationCode,
            verifyCode,
            cancelVerification,
        };
    },
};
</script>

<style scoped>
.pos-page-content {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    font-family: "Jost", sans-serif;
}

.telegram-section {
    max-width: 800px;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 2rem 0;
    font-family: "Jost", sans-serif;
}

.content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-box {
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.5rem;
    backdrop-filter: blur(8px);
}

.info-box.warning {
    background: #fffbeb;
    border-color: #fbbf24;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.info-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.info-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.6;
}

.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 500;
}

.telegram-badge {
    background: #f3f4f6;
    color: #374151;
}

.verified-badge {
    background: #d1fae5;
    color: #065f46;
}

.btn-link {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #003859;
    color: white;
    text-decoration: none;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0, 56, 89, 0.25);
}

.btn-link:hover {
    background: #002c4e;
    box-shadow: 0 4px 12px rgba(0, 56, 89, 0.3);
}

.instructions-box {
    background: white;
}

.instructions-list {
    margin: 1rem 0;
    padding-left: 1.5rem;
    color: #374151;
    line-height: 1.8;
}

.instructions-list li {
    margin-bottom: 0.75rem;
}

.instructions-list strong {
    color: #003859;
    font-weight: 600;
}

.alert {
    padding: 1rem;
    border-radius: 0;
    margin: 1rem 0;
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    color: #065f46;
}

.action-section {
    margin-top: 1.5rem;
}

.btn-primary {
    padding: 0.75rem 1.5rem;
    background: #003859;
    color: white;
    border: none;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.btn-primary:hover:not(:disabled) {
    background: #046490;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    padding: 0.75rem 1.5rem;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-send-code {
    width: 100%;
}

.code-input-wrapper {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.code-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #d1d5db;
    border-radius: 0;
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    letter-spacing: 0.5rem;
    font-family: "Jost", sans-serif;
    transition: all 0.2s;
}

.code-input:focus {
    outline: none;
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.1);
}

.code-hint {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.code-actions {
    display: flex;
    gap: 1rem;
}

.code-actions .btn-primary,
.code-actions .btn-secondary {
    flex: 1;
}

.benefits-box {
    background: #ecfdf5;
    border-color: #10b981;
}

.benefits-list {
    margin: 1rem 0 0 0;
    padding-left: 1.5rem;
    color: #374151;
    line-height: 1.8;
}

.benefits-list li {
    margin-bottom: 0.5rem;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 0;
    }

    .section-title {
        font-size: 1.125rem;
        margin-bottom: 0.75rem;
    }

    .info-box {
        padding: 0.75rem;
        border-radius: 0;
        margin-bottom: 0.75rem;
    }

    .info-title {
        font-size: 0.9375rem;
        margin-bottom: 0.5rem;
    }

    .info-text {
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .info-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .instructions-list {
        padding-left: 1.25rem;
        font-size: 0.8125rem;
        gap: 0.5rem;
    }

    .instructions-list li {
        margin-bottom: 0.5rem;
    }

    .btn-link,
    .btn-primary {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
        width: 100%;
        justify-content: center;
    }

    .qr-code-wrapper {
        padding: 0.75rem;
    }

    .qr-code {
        width: 200px;
        height: 200px;
    }

    .benefits-list {
        font-size: 0.8125rem;
        padding-left: 1.25rem;
    }
}
</style>
