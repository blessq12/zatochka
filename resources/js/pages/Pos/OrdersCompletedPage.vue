<template>
    <div class="pos-page-content">
        <div class="page-body">
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="orders.length === 0" class="empty-state">
                <p>Завершенных заказов нет</p>
            </div>
            <div v-else class="orders-list">
                <OrderCard
                    v-for="order in orders"
                    :key="order.id"
                    :order="order"
                    :primary-action="true"
                    primary-action-text="Вернуть в работу"
                    primary-action-loading-text="Сохранение..."
                    primary-action-class="btn-return-to-work"
                    :is-loading="isReturningToWork"
                    @view-details="openOrderDetails"
                    @primary-action="returnToWork"
                />
            </div>

            <OrderDetailsModal
                :is-open="isModalOpen"
                :order-id="selectedOrderId"
                @close="closeOrderDetails"
            />
        </div>
    </div>
</template>

<script>
import { reactive, ref, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { orderService } from "../../services/pos/OrderService.js";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { usePosStore } from "../../stores/posStore.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import { toastService } from "../../services/toastService.js";
import OrderDetailsModal from "../../components/Pos/OrderDetailsModal.vue";
import OrderCard from "../../components/Pos/OrderCard.vue";
import OrderStats from "../../components/Pos/OrderStats.vue";

export default {
    name: "OrdersCompletedPage",
    components: {
        OrderDetailsModal,
        OrderCard,
        OrderStats,
    },
    setup() {
        const route = useRoute();
        const orders = ref([]);
        const isLoading = ref(false);
        const isModalOpen = ref(false);
        const selectedOrderId = ref(null);
        const isReturningToWork = reactive({});
        const posStore = usePosStore();
        const { setNavigationItems, setCustomContent, reset } =
            useHeaderNavigation();

        const fetchOrders = async (silent = false) => {
            // Показываем индикатор загрузки только при первой загрузке или ручном обновлении
            if (!silent) {
                isLoading.value = true;
            }
            try {
                const newOrders = await orderService.getCompletedOrders();
                // Плавно обновляем список без моргания
                orders.value = newOrders;
            } catch (error) {
                console.error("Error fetching orders:", error);
            } finally {
                if (!silent) {
                    isLoading.value = false;
                }
            }
        };

        const returnToWork = async (orderId) => {
            if (isReturningToWork[orderId]) return;

            isReturningToWork[orderId] = true;
            try {
                await orderService.updateOrderStatus(orderId, "in_work");
                toastService.success("Заказ возвращен в работу");

                // Обновляем список заказов
                await fetchOrders();

                // Обновляем счетчики
                await posStore.getOrdersCount();
            } catch (error) {
                console.error("Error updating order status:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при изменении статуса заказа"
                );
            } finally {
                isReturningToWork[orderId] = false;
            }
        };

        const openOrderDetails = (orderId) => {
            selectedOrderId.value = orderId;
            isModalOpen.value = true;
        };

        const closeOrderDetails = () => {
            isModalOpen.value = false;
            selectedOrderId.value = null;
        };

        // Автообновление заказов каждые 20 секунд
        useAutoRefresh(fetchOrders, 20000, true);

        // Регистрация кастомного управления (кнопки со счетчиками) для экрана заказов
        onMounted(() => {
            setCustomContent({
                component: OrderStats,
                props: {},
            });
        });

        onUnmounted(() => {
            reset();
        });

        return {
            orders,
            isLoading,
            isModalOpen,
            selectedOrderId,
            isReturningToWork,
            openOrderDetails,
            closeOrderDetails,
            returnToWork,
        };
    },
};
</script>

<style scoped>
.pos-page-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.loading,
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.orders-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 8px;
    }

    .loading,
    .empty-state {
        padding: 2rem 1rem;
        font-size: 0.875rem;
    }

    .orders-list {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}
</style>
