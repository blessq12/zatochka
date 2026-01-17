<template>
    <div class="pos-page-content">
        <div class="page-body">
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="orders.length === 0" class="empty-state">
                <p>Новых заказов нет</p>
            </div>
            <div v-else class="orders-list">
                <OrderCard
                    v-for="order in orders"
                    :key="order.id"
                    :order="order"
                    :primary-action="true"
                    primary-action-text="Взять в работу"
                    primary-action-loading-text="Сохранение..."
                    primary-action-class="btn-status-in-work"
                    :is-loading="isUpdatingStatus"
                    @view-details="openOrderDetails"
                    @primary-action="changeStatusToInWork"
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
    name: "OrdersNewPage",
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
        const isUpdatingStatus = reactive({});
        const posStore = usePosStore();
        const { setNavigationItems, setCustomContent, reset } =
            useHeaderNavigation();

        const fetchOrders = async () => {
            isLoading.value = true;
            try {
                orders.value = await orderService.getNewOrders();
            } catch (error) {
                console.error("Error fetching orders:", error);
            } finally {
                isLoading.value = false;
            }
        };

        const changeStatusToInWork = async (orderId) => {
            if (isUpdatingStatus[orderId]) return;

            isUpdatingStatus[orderId] = true;
            try {
                await orderService.updateOrderStatus(orderId, "in_work");
                toastService.success("Заказ взят в работу");

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
                isUpdatingStatus[orderId] = false;
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
            isUpdatingStatus,
            openOrderDetails,
            closeOrderDetails,
            changeStatusToInWork,
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
</style>
