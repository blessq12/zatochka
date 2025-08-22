<template>
    <Modal
        :show="show"
        title="Оставить отзыв"
        size="md"
        :close-on-backdrop="false"
        :close-on-escape="true"
        :show-close-button="true"
        @close="handleClose"
    >
        <form @submit.prevent="submitReview" class="space-y-6">
            <!-- Рейтинг -->
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3"
                >
                    Ваша оценка
                </label>
                <div class="flex items-center space-x-2">
                    <button
                        v-for="star in 5"
                        :key="star"
                        type="button"
                        @click="form.rating = star"
                        :class="[
                            'text-2xl transition-colors duration-200',
                            star <= form.rating
                                ? 'text-yellow-400'
                                : 'text-gray-300 dark:text-gray-600',
                        ]"
                    >
                        <i class="mdi mdi-star"></i>
                    </button>
                    <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ form.rating }}/5
                    </span>
                </div>
            </div>

            <!-- Комментарий -->
            <div>
                <label
                    for="comment"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                >
                    Ваш отзыв
                </label>
                <textarea
                    id="comment"
                    v-model="form.comment"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent dark:bg-gray-700 dark:text-white"
                    placeholder="Поделитесь своим опытом работы с нами..."
                    required
                ></textarea>
            </div>

            <!-- Имя (опционально) -->
            <div>
                <label
                    for="name"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                >
                    Ваше имя (необязательно)
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent dark:bg-gray-700 dark:text-white"
                    placeholder="Как к вам обращаться?"
                />
            </div>
        </form>

        <template #footer>
            <button
                @click="handleClose"
                class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 transition-colors"
            >
                Отмена
            </button>
            <button
                @click="submitReview"
                :disabled="submitting || !form.rating || !form.comment.trim()"
                class="inline-flex items-center px-4 py-2 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i v-if="submitting" class="mdi mdi-loading mdi-spin mr-2"></i>
                <span v-else>Отправить отзыв</span>
            </button>
        </template>
    </Modal>
</template>

<script>
import Modal from "../Modal.vue";

export default {
    name: "ReviewModal",
    components: {
        Modal,
    },
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        entityType: {
            type: String,
            default: "App\\Models\\Company",
        },
        entityId: {
            type: Number,
            default: 1,
        },
        type: {
            type: String,
            default: "testimonial",
        },
    },
    emits: ["close", "submitted"],
    data() {
        return {
            form: {
                rating: 0,
                comment: "",
                name: "",
            },
            submitting: false,
        };
    },
    methods: {
        async submitReview() {
            if (!this.form.rating || !this.form.comment.trim()) {
                return;
            }

            this.submitting = true;
            try {
                const response = await fetch("/api/reviews", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                    },
                    body: JSON.stringify({
                        type: this.type,
                        entity_type: this.entityType,
                        entity_id: this.entityId,
                        rating: this.form.rating,
                        comment: this.form.comment,
                        source: "website",
                        metadata: {
                            user_name: this.form.name || "Анонимный клиент",
                        },
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Очищаем форму
                    this.form = {
                        rating: 0,
                        comment: "",
                        name: "",
                    };

                    // Закрываем модалку
                    this.handleClose();

                    // Уведомляем родительский компонент
                    this.$emit("submitted", data.data);

                    // Показываем уведомление
                    this.showNotification(
                        "Отзыв отправлен на модерацию!",
                        "success"
                    );
                } else {
                    this.showNotification(
                        "Ошибка при отправке отзыва",
                        "error"
                    );
                }
            } catch (error) {
                console.error("Ошибка отправки отзыва:", error);
                this.showNotification("Ошибка при отправке отзыва", "error");
            } finally {
                this.submitting = false;
            }
        },

        handleClose() {
            // Очищаем форму при закрытии
            this.form = {
                rating: 0,
                comment: "",
                name: "",
            };
            this.$emit("close");
        },

        showNotification(message, type = "info") {
            // Простое уведомление через alert, можно заменить на более красивое
            alert(message);
        },
    },
};
</script>
