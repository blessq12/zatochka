<template>
    <div class="pos-page-content">
        <div class="page-header">
            <h1>Завершенные заказы</h1>
        </div>
        <div class="page-body">
            <div v-if="isLoading" class="loading">Загрузка...</div>
            <div v-else-if="orders.length === 0" class="empty-state">
                <p>Завершенных заказов нет</p>
            </div>
            <div v-else class="orders-list">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="order-card"
                >
                    <div class="order-header">
                        <span class="order-number">{{ order.order_number }}</span>
                        <span class="order-status">{{ getStatusLabel(order.status) }}</span>
                    </div>
                    <div class="order-info">
                        <p><strong>Клиент:</strong> {{ order.client?.full_name }}</p>
                        <p><strong>Тип:</strong> {{ getTypeLabel(order.type) }}</p>
                        <p v-if="order.actual_price">
                            <strong>Цена:</strong> {{ formatPrice(order.actual_price) }} ₽
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";

export default {
    name: "OrdersCompletedPage",
    setup() {
        const orders = ref([]);
        const isLoading = ref(false);

        const fetchOrders = async () => {
            isLoading.value = true;
            try {
                const response = await axios.get("/api/pos/orders", {
                    params: { status: "completed" },
                });
                orders.value = response.data.orders || [];
            } catch (error) {
                console.error("Error fetching orders:", error);
            } finally {
                isLoading.value = false;
            }
        };

        const getStatusLabel = (status) => {
            const statuses = {
                ready: "Готов",
                issued: "Выдан",
                cancelled: "Отменен",
            };
            return statuses[status] || status;
        };

        const getTypeLabel = (type) => {
            const types = {
                repair: "Ремонт",
                sharpening: "Заточка",
                diagnostic: "Диагностика",
            };
            return types[type] || type;
        };

        const formatPrice = (price) => {
            return new Intl.NumberFormat("ru-RU").format(price);
        };

        onMounted(() => {
            fetchOrders();
        });

        return {
            orders,
            isLoading,
            getStatusLabel,
            getTypeLabel,
            formatPrice,
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

.page-header h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #003859;
    margin: 0 0 2rem 0;
    font-family: "Jost", sans-serif;
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

.order-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.2s;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.order-number {
    font-weight: 700;
    font-size: 1.125rem;
    color: #046490;
}

.order-status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    background: #d1fae5;
    color: #065f46;
}

.order-info p {
    margin: 0.5rem 0;
    color: #374151;
    font-size: 0.875rem;
}
</style>
