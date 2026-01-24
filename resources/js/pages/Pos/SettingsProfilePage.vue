<template>
    <div class="pos-page-content">
        <div class="page-header">
            <h1>Профиль</h1>
        </div>
        <div class="page-body">
            <div v-if="isLoading && !formData" class="loading">Загрузка...</div>
            <div v-else class="profile-card">
                <form @submit.prevent="handleSubmit" class="profile-form">
                    <!-- Информация только для чтения -->
                    <div class="form-section">
                        <h3 class="section-title">Учетная запись</h3>
                        <div class="info-row readonly">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ user?.email }}</span>
                        </div>
                    </div>

                    <!-- Редактируемые поля -->
                    <div class="form-section">
                        <h3 class="section-title">Личная информация</h3>

                        <!-- Имя -->
                        <div class="form-group">
                            <label class="form-label">Имя *</label>
                            <input
                                v-model="formData.name"
                                type="text"
                                class="form-input"
                                :class="{ 'form-input-error': errors.name }"
                                placeholder="Введите имя"
                            />
                            <p v-if="errors.name" class="form-error">
                                {{ errors.name }}
                            </p>
                        </div>

                        <!-- Фамилия -->
                        <div class="form-group">
                            <label class="form-label">Фамилия</label>
                            <input
                                v-model="formData.surname"
                                type="text"
                                class="form-input"
                                :class="{ 'form-input-error': errors.surname }"
                                placeholder="Введите фамилию"
                            />
                            <p v-if="errors.surname" class="form-error">
                                {{ errors.surname }}
                            </p>
                        </div>

                        <!-- Телефон -->
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <input
                                v-model="formData.phone"
                                v-maska
                                data-maska="+7 (###) ###-##-##"
                                type="tel"
                                class="form-input"
                                :class="{ 'form-input-error': errors.phone }"
                                placeholder="+7 (999) 123-45-67"
                            />
                            <p v-if="errors.phone" class="form-error">
                                {{ errors.phone }}
                            </p>
                        </div>

                        <!-- Telegram -->
                        <div class="form-group">
                            <label class="form-label">Telegram</label>
                            <div class="input-with-prefix">
                                <span class="input-prefix">@</span>
                                <input
                                    v-model="formData.telegram_username"
                                    type="text"
                                    class="form-input"
                                    :class="{
                                        'form-input-error':
                                            errors.telegram_username,
                                    }"
                                    placeholder="username"
                                />
                            </div>
                            <p
                                v-if="errors.telegram_username"
                                class="form-error"
                            >
                                {{ errors.telegram_username }}
                            </p>
                        </div>

                        <!-- Уведомления -->
                        <div class="form-group">
                            <label class="form-label checkbox-label">
                                <input
                                    v-model="formData.notifications_enabled"
                                    type="checkbox"
                                    class="form-checkbox"
                                />
                                <span>Получать уведомления</span>
                            </label>
                        </div>
                    </div>

                    <!-- Общая ошибка -->
                    <div v-if="errors.general" class="form-error-general">
                        {{ errors.general }}
                    </div>

                    <!-- Кнопки действий -->
                    <div class="form-actions">
                        <button
                            type="button"
                            @click="handleCancel"
                            class="btn btn-secondary"
                            :disabled="isSaving"
                        >
                            Отмена
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            :disabled="isSaving || !hasChanges"
                        >
                            <span v-if="isSaving">Сохранение...</span>
                            <span v-else>Сохранить изменения</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { usePosStore } from "../../stores/posStore.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import * as yup from "yup";

export default {
    name: "SettingsProfilePage",
    setup() {
        const route = useRoute();
        const posStore = usePosStore();
        const { setNavigationItems, reset } = useHeaderNavigation();

        const user = computed(() => posStore.user);
        const isLoading = computed(() => posStore.isLoading);

        const formData = ref(null);
        const originalData = ref(null);
        const errors = reactive({});
        const isSaving = ref(false);

        const validationSchema = yup.object().shape({
            name: yup
                .string()
                .required("Имя обязательно для заполнения")
                .max(255, "Имя слишком длинное"),
            surname: yup
                .string()
                .nullable()
                .max(255, "Фамилия слишком длинная"),
            phone: yup.string().nullable().max(255, "Телефон слишком длинный"),
            telegram_username: yup
                .string()
                .nullable()
                .max(255, "Telegram username слишком длинный"),
            notifications_enabled: yup.boolean(),
        });

        const initializeForm = () => {
            if (user.value) {
                formData.value = {
                    name: user.value.name || "",
                    surname: user.value.surname || "",
                    phone: user.value.phone || "",
                    telegram_username: user.value.telegram_username || "",
                    notifications_enabled:
                        user.value.notifications_enabled ?? true,
                };
                originalData.value = JSON.parse(JSON.stringify(formData.value));
            }
        };

        const hasChanges = computed(() => {
            if (!formData.value || !originalData.value) return false;
            return (
                JSON.stringify(formData.value) !==
                JSON.stringify(originalData.value)
            );
        });

        const handleSubmit = async () => {
            // Очищаем ошибки
            Object.keys(errors).forEach((key) => delete errors[key]);

            try {
                // Валидация
                await validationSchema.validate(formData.value, {
                    abortEarly: false,
                });

                isSaving.value = true;

                // Подготовка данных для отправки
                const dataToSend = {
                    name: formData.value.name,
                    surname: formData.value.surname || null,
                    phone: formData.value.phone || null,
                    telegram_username: formData.value.telegram_username || null,
                    notifications_enabled: formData.value.notifications_enabled,
                };

                const result = await posStore.updateProfile(dataToSend);

                if (result.success) {
                    // Обновляем originalData после успешного сохранения
                    originalData.value = JSON.parse(
                        JSON.stringify(formData.value)
                    );
                } else {
                    if (result.errors) {
                        Object.assign(errors, result.errors);
                    } else {
                        errors.general =
                            result.error || "Ошибка обновления профиля";
                    }
                }
            } catch (error) {
                if (error.inner) {
                    // Yup validation errors
                    error.inner.forEach((err) => {
                        errors[err.path] = err.message;
                    });
                } else if (error.response?.data?.errors) {
                    // Laravel validation errors
                    Object.assign(errors, error.response.data.errors);
                } else {
                    errors.general =
                        error.response?.data?.message ||
                        "Ошибка обновления профиля";
                }
            } finally {
                isSaving.value = false;
            }
        };

        const handleCancel = () => {
            if (originalData.value) {
                formData.value = JSON.parse(JSON.stringify(originalData.value));
            }
            Object.keys(errors).forEach((key) => delete errors[key]);
        };

        onMounted(async () => {
            // Регистрация элементов навигации в Header
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

            // Проверяем авторизацию при загрузке
            if (!posStore.isAuthenticated) {
                await posStore.getMe();
            }
            // Инициализируем форму после загрузки пользователя
            if (user.value) {
                initializeForm();
            }
        });

        onUnmounted(() => {
            reset();
        });

        // Следим за изменениями user для инициализации формы
        watch(
            () => user.value,
            (newUser) => {
                if (newUser && !formData.value) {
                    initializeForm();
                }
            },
            { immediate: true }
        );

        return {
            user,
            isLoading,
            formData,
            errors,
            isSaving,
            hasChanges,
            handleSubmit,
            handleCancel,
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

.page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 2rem 0;
    font-family: "Jost", sans-serif;
}

.loading {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.profile-card {
    max-width: 700px;
}

.profile-form {
    margin-bottom: 2rem;
}

.form-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 1rem 0;
    font-family: "Jost", sans-serif;
}

.info-row.readonly {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0;
    margin-bottom: 1rem;
}

.info-label {
    font-weight: 600;
    color: #374151;
}

.info-value {
    color: #6b7280;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0, 56, 89, 0.25);
    border-radius: 0;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: "Jost", sans-serif;
}

.form-input:focus {
    outline: none;
    border-color: #003859;
    box-shadow: 0 0 0 3px rgba(0, 56, 89, 0.15);
}

.form-input-error {
    border-color: #ef4444;
}

.form-input-error:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.input-with-prefix {
    display: flex;
    align-items: center;
}

.input-prefix {
    padding: 0.75rem 0.5rem 0.75rem 1rem;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-right: none;
    border-radius: 0;
    color: #6b7280;
    font-weight: 500;
}

.input-with-prefix .form-input {
    border-left: none;
    border-radius: 0;
}

.input-with-prefix .form-input-error {
    border-left: none;
}

.form-checkbox {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
    accent-color: #003859;
}

.form-error {
    margin-top: 0.5rem;
    color: #ef4444;
    font-size: 0.875rem;
}

.form-error-general {
    padding: 1rem;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 0;
    color: #991b1b;
    margin-bottom: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
    font-size: 1rem;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background: #003859;
    color: white;
    border-radius: 0;
    box-shadow: 0 2px 8px rgba(0, 56, 89, 0.25);
}

.btn-primary:hover:not(:disabled) {
    background: #002c4e;
    box-shadow: 0 4px 12px rgba(0, 56, 89, 0.3);
}

.btn-secondary {
    background: rgba(0, 56, 89, 0.08);
    color: #003859;
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
}

.btn-secondary:hover:not(:disabled) {
    background: rgba(0, 56, 89, 0.12);
    border-color: rgba(0, 56, 89, 0.3);
}

@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 0;
    }

    .page-header {
        margin-bottom: 0.75rem;
    }

    .page-header h1 {
        font-size: 1.125rem;
    }

    .profile-card {
        padding: 0.75rem;
        border-radius: 0;
    }

    .form-section {
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 0.9375rem;
        margin-bottom: 0.75rem;
    }

    .info-row {
        padding: 0.5rem 0;
        font-size: 0.8125rem;
    }

    .info-label {
        font-size: 0.75rem;
    }

    .info-value {
        font-size: 0.8125rem;
    }

    .form-group {
        margin-bottom: 0.75rem;
    }

    .form-label {
        font-size: 0.8125rem;
        margin-bottom: 0.375rem;
    }

    .form-input {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .form-error {
        font-size: 0.75rem;
        margin-top: 0.375rem;
    }

    .form-error-general {
        padding: 0.75rem;
        font-size: 0.8125rem;
        margin-bottom: 1rem;
    }

    .form-actions {
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn {
        width: 100%;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
    }
}
</style>
