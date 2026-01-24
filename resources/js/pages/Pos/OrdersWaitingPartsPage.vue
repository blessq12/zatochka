<template>
    <div class="pos-page-content">
        <div class="page-body">
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="orders.length === 0" class="empty-state">
                <p>Заказов в ожидании запчастей нет</p>
            </div>
            <div v-else class="orders-list">
                <OrderCard
                    v-for="order in orders"
                    :key="order.id"
                    :order="order"
                    :primary-action="true"
                    primary-action-text="В работу"
                    primary-action-class="btn-work"
                    @view-details="openOrderDetails"
                    @primary-action="goToOrderEdit"
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
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { orderService } from "../../services/pos/OrderService.js";
import { useAutoRefresh } from "../../composables/useAutoRefresh.js";
import { useHeaderNavigation } from "../../composables/useHeaderNavigation.js";
import OrderDetailsModal from "../../components/Pos/OrderDetailsModal.vue";
import OrderCard from "../../components/Pos/OrderCard.vue";
import OrderStats from "../../components/Pos/OrderStats.vue";

export default {
    name: "OrdersWaitingPartsPage",
    components: {
        OrderDetailsModal,
        OrderCard,
        OrderStats,
    },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const orders = ref([]);
        const isLoading = ref(false);
        const isModalOpen = ref(false);
        const selectedOrderId = ref(null);
        const { setCustomContent, reset } = useHeaderNavigation();

        const fetchOrders = async (silent = false) => {
            // Показываем индикатор загрузки только при первой загрузке или ручном обновлении
            if (!silent) {
                isLoading.value = true;
            }
            try {
                const newOrders = await orderService.getWaitingPartsOrders();
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

        const goToOrderEdit = (orderId) => {
            router.push({ name: "pos.orders.in-work", params: { id: orderId } });
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
            goToOrderEdit,
            openOrderDetails,
            closeOrderDetails,
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

.loading,
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    font-weight: 500;
}

.orders-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.25rem;
}

@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 0;
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
