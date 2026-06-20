<script>
import { mapStores } from "pinia";
import { useOrderStore } from "../../stores/orderStore.js";
import { formatServiceTypes } from "../../utils/serviceTypes.js";

export default {
    name: "ActiveOrdersSection",
    methods: {
        formatServiceTypes,
        formatDate(dateString) {
            if (!dateString) return "";
            const date = new Date(dateString);
            return date.toLocaleDateString("ru-RU", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
        formatPrice(price) {
            if (!price) return "—";
            return new Intl.NumberFormat("ru-RU", {
                style: "currency",
                currency: "RUB",
            }).format(price);
        },
    },
    computed: {
        ...mapStores(useOrderStore),
        activeOrders() {
            return this.orderStore.activeOrders || [];
        },
        isLoading() {
            return this.orderStore.isLoadingActive;
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
                АКТИВНЫЕ ЗАКАЗЫ
            </h2>

            <div v-if="isLoading" class="mt-4 text-center py-12">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#C20A6C] mx-auto mb-4"
                ></div>
                <p class="text-gray-600 dark:text-gray-400">
                    Загрузка заказов...
                </p>
            </div>

            <div
                v-else-if="activeOrders.length === 0"
                class="mt-4 text-center py-12 space-y-3"
            >
                <p
                    class="text-dark-gray-500 dark:text-gray-200 font-jost-regular text-base sm:text-lg"
                >
                    У вас пока нет активных заказов
                </p>
                <p
                    class="text-sm font-jost-regular text-dark-gray-400 dark:text-gray-400 max-w-md mx-auto"
                >
                    Если вы оформляли заявку без регистрации, заказы появятся
                    после привязки менеджером по номеру телефона.
                </p>
            </div>

            <div v-else class="mt-4 space-y-4">
                <div
                    v-for="order in activeOrders"
                    :key="order.id"
                    class="border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 py-6 bg-white/60 backdrop-blur-md dark:bg-gray-800/60 hover:shadow-lg transition-all duration-300"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                    >
                        <div class="flex-1">
                            <h3
                                class="text-lg sm:text-xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-3"
                            >
                                Заказ №{{ order.order_number }}
                            </h3>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm sm:text-base"
                            >
                                <div>
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Тип:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                    >
                                        {{ formatServiceTypes(order.service_types) }}
                                    </span>
                                </div>
                                <div>
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Создан:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                    >
                                        {{ formatDate(order.created_at) }}
                                    </span>
                                </div>
                                <div v-if="order.price">
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Стоимость:
                                    </span>
                                    <span
                                        class="ml-2 font-jost-bold text-[#C3006B]"
                                    >
                                        {{ formatPrice(order.price) }}
                                    </span>
                                </div>
                            </div>
                            <div
                                v-if="order.description"
                                class="mt-3 pt-3 border-t border-dark-blue-500/20 dark:border-dark-gray-200/20"
                            >
                                <p
                                    class="text-sm sm:text-base font-jost-regular text-dark-gray-500 dark:text-gray-300"
                                >
                                    <span
                                        class="font-jost-medium text-dark-gray-500 dark:text-gray-200"
                                    >
                                        Описание:
                                    </span>
                                    {{ order.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
