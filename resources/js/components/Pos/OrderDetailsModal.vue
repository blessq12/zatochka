<template>
    <div v-if="isOpen" class="modal-overlay" @click.self="close">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Заказ №{{ order?.order_number }}</h2>
                <button @click="close" class="modal-close-btn">✕</button>
            </div>
            <div class="modal-body">
                <div v-if="isLoading" class="loading">Загрузка...</div>
                <div v-else-if="!order" class="error-state">
                    <p>Заказ не найден</p>
                </div>
                <div v-else class="order-details">
                    <!-- Основная информация -->
                    <div class="detail-section">
                        <h3 class="section-title">Основная информация</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Номер заказа:</span>
                                <span class="detail-value">{{
                                    order.order_number
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Статус:</span>
                                <span class="detail-value">
                                    <span
                                        class="status-badge"
                                        :class="getStatusClass(order.status)"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Тип услуги:</span>
                                <span class="detail-value">{{
                                    getTypeLabel(order.service_type)
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Срочность:</span>
                                <span class="detail-value">
                                    <span
                                        class="urgency-badge"
                                        :class="
                                            order.urgency === 'urgent'
                                                ? 'urgent'
                                                : 'normal'
                                        "
                                    >
                                        {{
                                            order.urgency === "urgent"
                                                ? "Срочный"
                                                : "Обычный"
                                        }}
                                    </span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Дата создания:</span>
                                <span class="detail-value">{{
                                    formatDate(order.created_at)
                                }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"
                                    >Последнее обновление:</span
                                >
                                <span class="detail-value">{{
                                    formatDate(order.updated_at)
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Информация о клиенте -->
                    <div class="detail-section">
                        <h3 class="section-title">Клиент</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">ФИО:</span>
                                <span class="detail-value">{{
                                    order.client?.full_name || "—"
                                }}</span>
                            </div>
                            <div v-if="order.client?.phone" class="detail-item">
                                <span class="detail-label">Телефон:</span>
                                <span class="detail-value">{{
                                    order.client.phone
                                }}</span>
                            </div>
                            <div v-if="order.client?.email" class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">{{
                                    order.client.email
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Информация о филиале -->
                    <div class="detail-section">
                        <h3 class="section-title">Филиал</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Название:</span>
                                <span class="detail-value">{{
                                    order.branch?.name || "—"
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Оборудование -->
                    <div class="detail-section">
                        <h3 class="section-title">Оборудование</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Название:</span>
                                <span class="detail-value">{{
                                    order.equipment_name || "—"
                                }}</span>
                            </div>
                            <div
                                v-if="order.equipment_serial_number"
                                class="detail-item"
                            >
                                <span class="detail-label"
                                    >Серийный номер:</span
                                >
                                <span class="detail-value">{{
                                    order.equipment_serial_number
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Финансовая информация -->
                    <div class="detail-section">
                        <h3 class="section-title">Финансовая информация</h3>
                        <div class="detail-grid">
                            <div
                                v-if="order.estimated_price"
                                class="detail-item"
                            >
                                <span class="detail-label"
                                    >Ориентировочная цена:</span
                                >
                                <span class="detail-value price"
                                    >{{
                                        formatPrice(order.estimated_price)
                                    }}
                                    ₽</span
                                >
                            </div>
                            <div v-if="order.actual_price" class="detail-item">
                                <span class="detail-label"
                                    >Фактическая цена:</span
                                >
                                <span class="detail-value price"
                                    >{{
                                        formatPrice(order.actual_price)
                                    }}
                                    ₽</span
                                >
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Тип оплаты:</span>
                                <span class="detail-value">
                                    {{
                                        order.order_payment_type === "paid"
                                            ? "Оплачен"
                                            : "Гарантия"
                                    }}
                                </span>
                            </div>
                            <div v-if="order.delivery_cost" class="detail-item">
                                <span class="detail-label"
                                    >Стоимость доставки:</span
                                >
                                <span class="detail-value price"
                                    >{{
                                        formatPrice(order.delivery_cost)
                                    }}
                                    ₽</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Доставка -->
                    <div v-if="order.delivery_address" class="detail-section">
                        <h3 class="section-title">Доставка</h3>
                        <div class="detail-grid">
                            <div class="detail-item full-width">
                                <span class="detail-label"
                                    >Адрес доставки:</span
                                >
                                <span class="detail-value">{{
                                    order.delivery_address
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Описание проблемы -->
                    <div
                        v-if="order.problem_description"
                        class="detail-section"
                    >
                        <h3 class="section-title">Описание проблемы</h3>
                        <div class="detail-text">
                            {{ order.problem_description }}
                        </div>
                    </div>

                    <!-- Внутренние заметки -->
                    <div v-if="order.internal_notes" class="detail-section">
                        <h3 class="section-title">Внутренние заметки</h3>
                        <div class="detail-text">
                            {{ order.internal_notes }}
                        </div>
                    </div>

                    <!-- Выполненные работы -->
                    <div v-if="order.order_works && order.order_works.length > 0" class="detail-section">
                        <h3 class="section-title">Выполненные работы</h3>
                        <div class="works-list">
                            <div
                                v-for="work in order.order_works"
                                :key="work.id"
                                class="work-item"
                            >
                                <div class="work-header">
                                    <span class="work-price">{{ formatPrice(work.work_price || 0) }} ₽</span>
                                </div>
                                <div class="work-description">{{ work.description }}</div>
                                <div v-if="work.warehouse_items && work.warehouse_items.length > 0" class="work-materials">
                                    <div class="materials-title">Материалы:</div>
                                    <div class="materials-list">
                                        <div
                                            v-for="material in work.warehouse_items"
                                            :key="material.id"
                                            class="material-item"
                                        >
                                            <span class="material-name">{{ material.name }}</span>
                                            <span v-if="material.article" class="material-article">Арт: {{ material.article }}</span>
                                            <span class="material-quantity">Количество: {{ material.pivot?.quantity || 0 }}</span>
                                            <span class="material-price">Цена: {{ formatPrice(material.pivot?.price || 0) }} ₽</span>
                                            <span class="material-total">Сумма: {{ formatPrice((material.pivot?.quantity || 0) * (material.pivot?.price || 0)) }} ₽</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="works-summary">
                            <div class="summary-row">
                                <span class="summary-label">Стоимость работ:</span>
                                <span class="summary-value">{{ formatPrice(totalWorksPrice) }} ₽</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Стоимость материалов:</span>
                                <span class="summary-value">{{ formatPrice(totalMaterialsPrice) }} ₽</span>
                            </div>
                            <div class="summary-row total">
                                <span class="summary-label">Итого:</span>
                                <span class="summary-value">{{ formatPrice(totalWorksPrice + totalMaterialsPrice) }} ₽</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ответственные -->
                    <div class="detail-section">
                        <h3 class="section-title">Ответственные</h3>
                        <div class="detail-grid">
                            <div v-if="order.manager" class="detail-item">
                                <span class="detail-label">Менеджер:</span>
                                <span class="detail-value">{{
                                    order.manager.name
                                }}</span>
                            </div>
                            <div v-if="order.master" class="detail-item">
                                <span class="detail-label">Мастер:</span>
                                <span class="detail-value">
                                    {{
                                        order.master.surname
                                            ? `${order.master.surname} ${order.master.name}`
                                            : order.master.name
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, ref, watch } from "vue";
import { orderService } from "../../services/pos/OrderService.js";

export default {
    name: "OrderDetailsModal",
    props: {
        isOpen: {
            type: Boolean,
            required: true,
        },
        orderId: {
            type: [Number, String],
            default: null,
        },
    },
    emits: ["close"],
    setup(props, { emit }) {
        const order = ref(null);
        const isLoading = ref(false);

        const totalWorksPrice = computed(() => {
            if (!order.value?.order_works) return 0;
            return order.value.order_works.reduce((sum, work) => {
                return sum + (parseFloat(work.work_price) || 0);
            }, 0);
        });

        const totalMaterialsPrice = computed(() => {
            if (!order.value?.order_works) return 0;
            let total = 0;
            order.value.order_works.forEach((work) => {
                if (work.warehouse_items) {
                    work.warehouse_items.forEach((material) => {
                        const quantity = parseFloat(material.pivot?.quantity) || 0;
                        const price = parseFloat(material.pivot?.price) || 0;
                        total += quantity * price;
                    });
                }
            });
            return total;
        });

        const fetchOrder = async (orderId) => {
            if (!orderId) {
                order.value = null;
                return;
            }

            isLoading.value = true;
            try {
                order.value = await orderService.getOrderById(orderId);
            } catch (error) {
                console.error("Error fetching order:", error);
                if (error.response?.status === 404) {
                    order.value = null;
                }
            } finally {
                isLoading.value = false;
            }
        };

        const close = () => {
            emit("close");
        };

        const formatDate = (dateString) => {
            if (!dateString) return "—";
            const date = new Date(dateString);
            return new Intl.DateTimeFormat("ru-RU", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            }).format(date);
        };

        const getStatusClass = (status) => {
            const classes = {
                new: "status-new",
                consultation: "status-consultation",
                diagnostic: "status-diagnostic",
                in_work: "status-in-work",
                waiting_parts: "status-waiting-parts",
                ready: "status-ready",
                issued: "status-issued",
                cancelled: "status-cancelled",
            };
            return classes[status] || "";
        };

        // Загружаем заказ при открытии модалки или изменении orderId
        watch(
            () => [props.isOpen, props.orderId],
            ([isOpen, orderId]) => {
                if (isOpen && orderId) {
                    fetchOrder(orderId);
                } else if (!isOpen) {
                    order.value = null;
                }
            },
            { immediate: true }
        );

        // Закрытие по Escape
        const handleEscape = (e) => {
            if (e.key === "Escape" && props.isOpen) {
                close();
            }
        };

        if (typeof window !== "undefined") {
            window.addEventListener("keydown", handleEscape);
        }

        return {
            order,
            isLoading,
            totalWorksPrice,
            totalMaterialsPrice,
            close,
            formatDate,
            getStatusLabel: orderService.getStatusLabel,
            getTypeLabel: orderService.getTypeLabel,
            formatPrice: orderService.formatPrice,
            getStatusClass,
        };
    },
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 2rem;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-container {
    background: white;
    border-radius: 12px;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 900;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.modal-close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f3f4f6;
    border-radius: 8px;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Jost", sans-serif;
}

.modal-close-btn:hover {
    background: #e5e7eb;
    color: #374151;
}

.modal-body {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

.loading,
.error-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.order-details {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.detail-section {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1.25rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 1rem 0;
    font-family: "Jost", sans-serif;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 0.875rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 0.9375rem;
    color: #1f2937;
    font-weight: 500;
}

.detail-value.price {
    font-weight: 700;
    color: #003859;
    font-size: 1rem;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8125rem;
    font-weight: 600;
    display: inline-block;
}

.status-new {
    background: #dbeafe;
    color: #1e40af;
}

.status-consultation {
    background: #fef3c7;
    color: #92400e;
}

.status-diagnostic {
    background: #dbeafe;
    color: #1e40af;
}

.status-in-work {
    background: #fef3c7;
    color: #92400e;
}

.status-waiting-parts {
    background: #fee2e2;
    color: #991b1b;
}

.status-ready {
    background: #d1fae5;
    color: #065f46;
}

.status-issued {
    background: #e5e7eb;
    color: #374151;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.urgency-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8125rem;
    font-weight: 600;
    display: inline-block;
}

.urgency-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.urgency-badge.normal {
    background: #dbeafe;
    color: #1e40af;
}

.detail-text {
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
    font-size: 0.9375rem;
}

.works-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.work-item {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 1rem;
    background: white;
}

.work-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.work-price {
    font-weight: 600;
    color: #059669;
    font-size: 1.125rem;
}

.work-description {
    color: #374151;
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.work-materials {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.materials-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.materials-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.material-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 4px;
    font-size: 0.8125rem;
}

.material-name {
    font-weight: 600;
    color: #374151;
}

.material-article {
    color: #6b7280;
}

.material-quantity,
.material-price,
.material-total {
    color: #6b7280;
}

.material-total {
    font-weight: 600;
    color: #046490;
}

.works-summary {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
}

.works-summary .summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.875rem;
}

.works-summary .summary-row.total {
    font-weight: 600;
    font-size: 1rem;
    color: #003859;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 0.5rem;
}

.works-summary .summary-label {
    color: #6b7280;
}

.works-summary .summary-value {
    color: #374151;
    font-weight: 600;
}

.works-summary .summary-row.total .summary-value {
    color: #003859;
    font-size: 1.125rem;
}
</style>
