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
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th colspan="2" class="table-header">
                                    <div class="header-content">
                                        <span>Заказ №{{ order.order_number }}</span>
                                        <span
                                            class="status-badge"
                                            :class="getStatusClass(order.status)"
                                        >
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-label">Тип услуги</td>
                                <td class="table-value">{{ getTypeLabel(order.service_type) }}</td>
                            </tr>
                            <tr>
                                <td class="table-label">Срочность</td>
                                <td class="table-value">
                                    <span
                                        class="urgency-badge"
                                        :class="order.urgency === 'urgent' ? 'urgent' : 'normal'"
                                    >
                                        {{ order.urgency === "urgent" ? "Срочный" : "Обычный" }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label">Филиал</td>
                                <td class="table-value">{{ order.branch?.name || "—" }}</td>
                            </tr>
                            <tr>
                                <td class="table-label">Дата создания</td>
                                <td class="table-value">{{ formatDate(order.created_at) }}</td>
                            </tr>
                            <tr>
                                <td class="table-label">Последнее обновление</td>
                                <td class="table-value">{{ formatDate(order.updated_at) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Клиент -->
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th colspan="2" class="table-header">Клиент</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-label">ФИО</td>
                                <td class="table-value">{{ order.client?.full_name || "—" }}</td>
                            </tr>
                            <tr v-if="order.client?.phone">
                                <td class="table-label">Телефон</td>
                                <td class="table-value">{{ order.client.phone }}</td>
                            </tr>
                            <tr v-if="order.client?.email">
                                <td class="table-label">Email</td>
                                <td class="table-value">{{ order.client.email }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Оборудование -->
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th colspan="2" class="table-header">Оборудование</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-label">Название</td>
                                <td class="table-value">{{ order.equipment_name || "—" }}</td>
                            </tr>
                            <tr v-if="order.equipment_serial_number">
                                <td class="table-label">Серийный номер</td>
                                <td class="table-value">{{ order.equipment_serial_number }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Описание проблемы -->
                    <table v-if="order.problem_description" class="info-table">
                        <thead>
                            <tr>
                                <th class="table-header">Описание проблемы</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-text">{{ order.problem_description }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Работы -->
                    <table v-if="order.order_works && order.order_works.length > 0" class="info-table">
                        <thead>
                            <tr>
                                <th class="table-header">Описание работы</th>
                                <th class="table-header text-right">Стоимость</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="work in order.order_works" :key="work.id">
                                <td class="table-value">{{ work.description }}</td>
                                <td class="table-value text-right price">{{ formatPrice(work.work_price || 0) }} ₽</td>
                            </tr>
                            <tr class="table-total">
                                <td class="table-label">Итого работ</td>
                                <td class="table-value text-right price">{{ formatPrice(totalWorksPrice) }} ₽</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Материалы -->
                    <table v-if="order.order_works && order.order_works.some(w => w.materials && w.materials.length > 0)" class="info-table">
                        <thead>
                            <tr>
                                <th class="table-header">Название</th>
                                <th class="table-header">Артикул</th>
                                <th class="table-header text-right">Количество</th>
                                <th class="table-header text-right">Цена</th>
                                <th class="table-header text-right">Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="work in order.order_works" :key="work.id">
                                <tr v-for="material in work.materials" :key="material.id">
                                    <td class="table-value">{{ material.name }}</td>
                                    <td class="table-value">{{ material.article || "—" }}</td>
                                    <td class="table-value text-right">{{ material.quantity || 0 }} {{ material.unit || "шт" }}</td>
                                    <td class="table-value text-right">{{ formatPrice(material.price || 0) }} ₽</td>
                                    <td class="table-value text-right price">{{ formatPrice((material.quantity || 0) * (material.price || 0)) }} ₽</td>
                                </tr>
                            </template>
                            <tr class="table-total">
                                <td colspan="4" class="table-label">Итого материалов</td>
                                <td class="table-value text-right price">{{ formatPrice(totalMaterialsPrice) }} ₽</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Финансовая информация -->
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th colspan="2" class="table-header">Финансовая информация</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="order.estimated_price">
                                <td class="table-label">Ориентировочная цена</td>
                                <td class="table-value price">{{ formatPrice(order.estimated_price) }} ₽</td>
                            </tr>
                            <tr v-if="order.actual_price">
                                <td class="table-label">Фактическая цена</td>
                                <td class="table-value price">{{ formatPrice(order.actual_price) }} ₽</td>
                            </tr>
                            <tr>
                                <td class="table-label">Тип оплаты</td>
                                <td class="table-value">
                                    {{ order.order_payment_type === "paid" ? "Оплачен" : "Гарантия" }}
                                </td>
                            </tr>
                            <tr v-if="order.delivery_cost">
                                <td class="table-label">Стоимость доставки</td>
                                <td class="table-value price">{{ formatPrice(order.delivery_cost) }} ₽</td>
                            </tr>
                            <tr v-if="order.delivery_address">
                                <td class="table-label">Адрес доставки</td>
                                <td class="table-value">{{ order.delivery_address }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Ответственные -->
                    <table v-if="order.manager || order.master" class="info-table">
                        <thead>
                            <tr>
                                <th colspan="2" class="table-header">Ответственные</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="order.manager">
                                <td class="table-label">Менеджер</td>
                                <td class="table-value">{{ order.manager.name }}</td>
                            </tr>
                            <tr v-if="order.master">
                                <td class="table-label">Мастер</td>
                                <td class="table-value">
                                    {{ order.master.surname ? `${order.master.surname} ${order.master.name}` : order.master.name }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Внутренние заметки -->
                    <table v-if="order.internal_notes" class="info-table">
                        <thead>
                            <tr>
                                <th class="table-header">Внутренние заметки</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="table-text">{{ order.internal_notes }}</td>
                            </tr>
                        </tbody>
                    </table>
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
                if (work.materials) {
                    work.materials.forEach((material) => {
                        const quantity = parseFloat(material.quantity) || 0;
                        const price = parseFloat(material.price) || 0;
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

.info-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 0;
}

.info-table thead {
    background: #003859;
    color: white;
}

.info-table thead th {
    text-align: left;
}

.table-header {
    padding: 1rem 1.25rem;
    font-size: 1rem;
    font-weight: 700;
    text-align: left;
    font-family: "Jost", sans-serif;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.info-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

.info-table tbody tr:last-child {
    border-bottom: none;
}

.info-table tbody tr:hover {
    background: #f9fafb;
}

.table-label {
    padding: 0.875rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    width: 40%;
    vertical-align: top;
}

.table-value {
    padding: 0.875rem 1.25rem;
    font-size: 0.9375rem;
    color: #1f2937;
    font-weight: 500;
    vertical-align: top;
}

.table-value.price {
    font-weight: 700;
    color: #003859;
    font-size: 1rem;
}

.table-text {
    padding: 1rem 1.25rem;
    font-size: 0.9375rem;
    color: #374151;
    line-height: 1.7;
    white-space: pre-wrap;
}

.text-right {
    text-align: right;
}

.table-total {
    background: #f9fafb;
    font-weight: 700;
}

.table-total .table-label {
    color: #003859;
    font-size: 0.9375rem;
}

.table-total .table-value {
    color: #003859;
    font-size: 1.125rem;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    display: inline-block;
    white-space: nowrap;
}

.status-new {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-consultation {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-diagnostic {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-in-work {
    background: rgba(255, 255, 255, 0.2);
    color: white;
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
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
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

</style>
