<template>
    <div class="client-orders" ref="container">
        <!-- Загрузка -->
        <div v-if="loading" class="text-center py-8">
            <i class="mdi mdi-loading mdi-spin text-4xl text-accent mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">Загрузка заказов...</p>
        </div>

        <!-- Не авторизован -->
        <div v-else-if="!isAuthenticated" class="text-center py-8">
            <i class="mdi mdi-account-lock text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">
                Для просмотра заказов необходимо войти в аккаунт
            </p>
        </div>

        <!-- Нет заказов -->
        <div v-else-if="!hasOrders" class="text-center py-8">
            <i class="mdi mdi-clipboard-list text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400 mb-2">
                У вас пока нет заказов
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
                Оставьте заявку на заточку или ремонт инструмента
            </p>
            <div class="mt-4 space-x-3">
                <button
                    @click="showSharpeningForm = true"
                    class="inline-flex items-center px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent-dark transition-colors"
                >
                    <i class="mdi mdi-tools mr-2"></i>
                    Заточка
                </button>
                <button
                    @click="showRepairForm = true"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    <i class="mdi mdi-wrench mr-2"></i>
                    Ремонт
                </button>
            </div>
        </div>

        <!-- Список заказов -->
        <div v-else class="space-y-4">
            <!-- Кнопка создания нового заказа -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Ваши заказы
                </h3>
                <div class="space-x-2">
                    <button
                        @click="showSharpeningForm = true"
                        class="inline-flex items-center px-3 py-2 bg-accent text-white text-sm rounded-lg hover:bg-accent-dark transition-colors"
                    >
                        <i class="mdi mdi-tools mr-1"></i>
                        Заточка
                    </button>
                    <button
                        @click="showRepairForm = true"
                        class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <i class="mdi mdi-wrench mr-1"></i>
                        Ремонт
                    </button>
                </div>
            </div>
            <div
                v-for="order in orders"
                :key="order.id"
                class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
                <!-- Заголовок заказа -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <span
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            #{{ order.order_number }}
                        </span>
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-full font-medium',
                                getStatusClasses(order.status),
                            ]"
                        >
                            {{ getStatusText(order.status) }}
                        </span>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(order.created_at) }}
                    </span>
                </div>

                <!-- Информация о заказе -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Услуга:</span
                        >
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ getServiceText(order.service_type) }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Инструмент:</span
                        >
                        <span class="text-gray-900 dark:text-white">
                            {{ order.tool_type }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Количество:</span
                        >
                        <span class="text-gray-900 dark:text-white">
                            {{ order.total_tools_count }} шт.
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Сумма:</span
                        >
                        <span
                            class="text-gray-900 dark:text-white font-semibold"
                        >
                            {{ formatPrice(order.total_amount) }} ₽
                        </span>
                    </div>
                </div>

                <!-- Описание проблемы -->
                <div
                    v-if="order.problem_description"
                    class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600"
                >
                    <div class="text-sm">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Проблема:</span
                        >
                        <p class="text-gray-900 dark:text-white mt-1">
                            {{ order.problem_description }}
                        </p>
                    </div>
                </div>

                <!-- Описание выполненной работы -->
                <div
                    v-if="order.work_description"
                    class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600"
                >
                    <div class="text-sm">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Выполненная работа:</span
                        >
                        <p class="text-gray-900 dark:text-white mt-1">
                            {{ order.work_description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для формы заточки -->
        <Modal
            :show="showSharpeningForm"
            title="Заявка на заточку"
            size="xl"
            @close="showSharpeningForm = false"
        >
            <div class="max-h-[70vh] overflow-y-auto">
                <sharpening-order-form
                    @order-created="handleOrderCreated"
                    @close="showSharpeningForm = false"
                />
            </div>
        </Modal>

        <!-- Модальное окно для формы ремонта -->
        <Modal
            :show="showRepairForm"
            title="Заявка на ремонт"
            size="xl"
            @close="showRepairForm = false"
        >
            <div class="max-h-[70vh] overflow-y-auto">
                <repair-order-form
                    @order-created="handleOrderCreated"
                    @close="showRepairForm = false"
                />
            </div>
        </Modal>
    </div>
</template>

<script>
import { useAuthStore } from "../stores/auth.js";

export default {
    name: "ClientOrders",

    data() {
        return {
            loading: true,
            orders: [],
            authStore: null,
            showSharpeningForm: false,
            showRepairForm: false,
        };
    },

    computed: {
        isAuthenticated() {
            return this.authStore?.isAuthenticated;
        },

        hasOrders() {
            return this.orders.length > 0;
        },
    },

    async mounted() {
        this.authStore = useAuthStore();

        // Проверяем авторизацию и загружаем заказы
        if (this.isAuthenticated) {
            await this.loadOrders();
        }

        this.loading = false;
    },

    watch: {
        "authStore.isAuthenticated": {
            async handler(newValue) {
                if (newValue && !this.loading) {
                    this.loading = true;
                    await this.loadOrders();
                    this.loading = false;
                }
            },
            immediate: false,
        },
    },

    methods: {
        async loadOrders() {
            try {
                const response = await fetch("/api/client/orders", {
                    method: "GET",
                    headers: this.authStore.getHeaders(),
                });

                if (!response.ok) {
                    throw new Error("Ошибка загрузки заказов");
                }

                const data = await response.json();
                this.orders = data.data || [];
            } catch (error) {
                console.error("Ошибка загрузки заказов:", error);
                this.orders = [];
            }
        },

        getStatusClasses(status) {
            const classes = {
                new: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
                in_progress:
                    "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
                ready: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
                delivered:
                    "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
                cancelled:
                    "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
                master_received:
                    "bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200",
                in_work:
                    "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
                courier_delivery:
                    "bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200",
            };
            return (
                classes[status] ||
                "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200"
            );
        },

        getStatusText(status) {
            const statuses = {
                new: "Новый",
                in_progress: "В работе",
                ready: "Готов",
                delivered: "Доставлен",
                cancelled: "Отменен",
                master_received: "Принят мастером",
                in_work: "В работе",
                courier_delivery: "Передан курьеру",
            };
            return statuses[status] || status;
        },

        getServiceText(service) {
            const services = {
                repair: "Ремонт",
                maintenance: "Заточка",
                consultation: "Консультация",
                other: "Другое",
            };
            return services[service] || service;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("ru-RU", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatPrice(price) {
            return new Intl.NumberFormat("ru-RU").format(price);
        },

        async handleOrderCreated() {
            // Закрываем модальные окна
            this.showSharpeningForm = false;
            this.showRepairForm = false;

            // Перезагружаем заказы
            await this.loadOrders();
        },
    },
};
</script>
