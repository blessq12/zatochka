<template>
    <Teleport to="body">
        <div v-if="isOpen" class="modal-overlay" @click.self="close">
            <div class="modal-container">
                <div class="modal-header">
                    <h2 class="modal-title">
                        Заказ №{{ order?.order_number }}
                    </h2>
                    <button @click="close" class="modal-close-btn">✕</button>
                </div>
                <div class="modal-body">
                    <div v-if="isLoading" class="loading">Загрузка...</div>
                    <div v-else-if="!order" class="error-state">
                        <p>Заказ не найден</p>
                    </div>
                    <div v-else class="order-details">
                        <!-- Основная информация -->
                        <div class="details-section">
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Основная информация
                                </h3>
                            </div>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label"
                                        >Номер заказа</span
                                    >
                                    <span class="detail-value"
                                        >№{{ order.order_number }}</span
                                    >
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Статус</span>
                                    <span
                                        class="detail-badge"
                                        :class="getStatusClass(order.status)"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Тип услуги</span>
                                    <span class="detail-value">{{
                                        getTypeLabel(order.service_type)
                                    }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Срочность</span>
                                    <span
                                        class="detail-badge urgency"
                                        :class="
                                            order.urgency === 'urgent'
                                                ? 'urgent'
                                                : 'normal'
                                        "
                                    >
                                        {{
                                            order.urgency === "urgent"
                                                ? "⚡ Срочный"
                                                : "Обычный"
                                        }}
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Филиал</span>
                                    <span class="detail-value">{{
                                        order.branch?.name || "—"
                                    }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label"
                                        >Дата создания</span
                                    >
                                    <span class="detail-value">{{
                                        formatDate(order.created_at)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Оборудование -->
                        <div
                            v-if="order.equipment?.name || order.equipment_name"
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Оборудование
                                </h3>
                            </div>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Название</span>
                                    <span class="detail-value">
                                        {{
                                            order.equipment?.name ||
                                            order.equipment_name
                                        }}
                                    </span>
                                </div>
                                <div
                                    v-if="
                                        order.equipment?.serial_numbers_display ||
                                        order.equipment_serial_number
                                    "
                                    class="detail-item"
                                >
                                    <span class="detail-label"
                                        >Серийный номер</span
                                    >
                                    <span class="detail-value">
                                        {{
                                            order.equipment?.serial_numbers_display ||
                                            order.equipment_serial_number
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Инструменты для заточки -->
                        <div
                            v-if="order.tools && order.tools.length > 0"
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Инструменты
                                </h3>
                            </div>
                            <div class="tools-details">
                                <div
                                    v-for="(tool, idx) in order.tools"
                                    :key="tool.id || idx"
                                    class="tool-detail-item"
                                >
                                    <span class="tool-type">{{
                                        tool.tool_type_label || tool.tool_type
                                    }}</span>
                                    <span class="tool-quantity"
                                        >Количество: {{ tool.quantity }}</span
                                    >
                                    <span
                                        v-if="tool.description"
                                        class="tool-description"
                                        >{{ tool.description }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Описание проблемы -->
                        <div
                            v-if="order.problem_description"
                            class="details-section problem-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Описание проблемы
                                </h3>
                            </div>
                            <div class="problem-content">
                                {{ order.problem_description }}
                            </div>
                        </div>

                        <!-- Работы -->
                        <div
                            v-if="
                                order.order_works &&
                                order.order_works.length > 0
                            "
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <span class="details-icon">🔨</span>
                                <h3 class="details-section-title">
                                    Выполненные работы
                                </h3>
                            </div>
                            <div class="works-list-details">
                                <div
                                    v-for="work in order.order_works"
                                    :key="work.id"
                                    class="work-detail-item"
                                >
                                    <div class="work-detail-content">
                                        <p class="work-detail-description">
                                            {{ work.description }}
                                        </p>
                                        <p
                                            v-if="work.equipment_component_name || work.equipment_component_serial_number"
                                            class="work-detail-equipment-component"
                                        >
                                            Элемент: {{ work.equipment_component_name || 'Не указан' }}{{ work.equipment_component_serial_number ? ` (SN: ${work.equipment_component_serial_number})` : '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Материалы (привязаны к заказу) -->
                        <div
                            v-if="orderMaterialsList.length > 0"
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Материалы и запчасти
                                </h3>
                            </div>
                            <div class="materials-list-details">
                                <div
                                    v-for="material in orderMaterialsList"
                                    :key="material.id"
                                    class="material-detail-item"
                                >
                                    <div class="material-detail-info">
                                        <span class="material-detail-name">{{
                                            material.name
                                        }}</span>
                                        <span
                                            v-if="material.article"
                                            class="material-detail-article"
                                        >
                                            Арт: {{ material.article }}
                                        </span>
                                    </div>
                                    <div class="material-detail-quantity">
                                        {{ material.quantity || 0 }}
                                        {{ material.unit || "шт" }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация об оплате и доставке -->
                        <div class="details-section financial-section">
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Дополнительная информация
                                </h3>
                            </div>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Тип оплаты</span>
                                    <span
                                        class="detail-badge payment-type"
                                        :class="
                                            order.order_payment_type === 'paid'
                                                ? 'paid'
                                                : 'warranty'
                                        "
                                    >
                                        {{
                                            order.order_payment_type === "paid"
                                                ? "Платный"
                                                : "Гарантийный"
                                        }}
                                    </span>
                                </div>
                                <div
                                    v-if="order.delivery_address"
                                    class="detail-item full-width"
                                >
                                    <span class="detail-label"
                                        >Адрес доставки</span
                                    >
                                    <span class="detail-value">{{
                                        order.delivery_address
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ответственные -->
                        <div
                            v-if="order.manager || order.master"
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Ответственные
                                </h3>
                            </div>
                            <div class="details-grid">
                                <div v-if="order.manager" class="detail-item">
                                    <span class="detail-label">Менеджер</span>
                                    <span class="detail-value">{{
                                        order.manager.name
                                    }}</span>
                                </div>
                                <div v-if="order.master" class="detail-item">
                                    <span class="detail-label">Мастер</span>
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

                        <!-- Внутренние заметки -->
                        <div
                            v-if="order.internal_notes"
                            class="details-section notes-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Внутренние заметки
                                </h3>
                            </div>
                            <div class="notes-content">
                                {{ order.internal_notes }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
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

        const orderMaterialsList = computed(() => {
            return order.value?.order_materials ?? [];
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
            orderMaterialsList,
            close,
            formatDate,
            getStatusLabel: orderService.getStatusLabel,
            getTypeLabel: orderService.getTypeLabel,
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
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
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
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s;
    overflow: hidden;
    font-family: "Jost", sans-serif;
}

@media (max-width: 768px) {
    .modal-overlay {
        padding: 0.5rem;
    }

    .modal-container {
        max-height: 95vh;
        border-radius: 0;
    }

    .modal-header {
        padding: 1rem 1.25rem;
    }

    .modal-title {
        font-size: 1.125rem;
    }

    .modal-body {
        padding: 1rem;
    }

    .order-details {
        gap: 1rem;
    }

    .details-section {
        padding: 0.75rem;
    }

    .details-section-header {
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
    }

    .details-section-title {
        font-size: 1rem;
    }

    .details-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .detail-value {
        font-size: 0.875rem;
    }


    .material-detail-item {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .material-detail-quantity {
        text-align: left;
    }
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
    padding: 1.25rem 1.5rem;
    background: #c20a6c;
    border-bottom: none;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: white;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.modal-close-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 0;
    font-size: 1.125rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Jost", sans-serif;
}

.modal-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
    background: #f9fafb;
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

.details-section {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    padding: 1.25rem;
    transition: all 0.2s;
}

.details-section:hover {
    border-color: rgba(0, 56, 89, 0.35);
    box-shadow: 0 4px 12px rgba(0, 56, 89, 0.08);
}

.details-section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(0, 56, 89, 0.15);
}

.details-icon {
    font-size: 1.25rem;
}

.details-section-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #374151;
    word-break: break-word;
}

.detail-value.link {
    color: #003859;
    text-decoration: none;
}

.detail-value.link:hover {
    text-decoration: underline;
}


.detail-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0;
    font-size: 0.8125rem;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
    font-family: "Jost", sans-serif;
}

.detail-badge.status-new {
    background: #dbeafe;
    color: #1e40af;
}

.detail-badge.status-in-work,
.detail-badge.status-waiting-parts {
    background: #fef3c7;
    color: #92400e;
}

.detail-badge.status-ready {
    background: #d1fae5;
    color: #065f46;
}

.detail-badge.status-issued {
    background: #dbeafe;
    color: #1e40af;
}

.detail-badge.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.detail-badge.urgency.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.detail-badge.urgency.normal {
    background: #dbeafe;
    color: #1e40af;
}

.detail-badge.payment-type.paid {
    background: #d1fae5;
    color: #065f46;
}

.detail-badge.payment-type.warranty {
    background: #fef3c7;
    color: #92400e;
}

.problem-section {
    background: rgba(254, 243, 199, 0.8);
    border-color: rgba(0, 56, 89, 0.2);
}

.problem-content {
    font-size: 0.875rem;
    color: #78350f;
    line-height: 1.6;
    white-space: pre-wrap;
}

.tools-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tool-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0;
}

.tool-type {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.tool-quantity {
    font-size: 0.8125rem;
    color: #6b7280;
}

.tool-description {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

.works-list-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.work-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0;
}

.work-detail-content {
    flex: 1;
    min-width: 0;
}

.work-detail-description {
    margin: 0;
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
}

.work-detail-equipment-component {
    margin: 0.5rem 0 0 0;
    font-size: 0.75rem;
    color: #6b7280;
    font-style: italic;
}

.work-detail-equipment-component {
    margin: 0.5rem 0 0 0;
    font-size: 0.75rem;
    color: #6b7280;
    font-style: italic;
}


.materials-list-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.material-detail-item {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0;
}

.material-detail-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.material-detail-name {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    word-break: break-word;
}

.material-detail-article {
    font-size: 0.75rem;
    color: #6b7280;
    font-family: monospace;
}

.material-detail-quantity {
    font-size: 0.8125rem;
    color: #6b7280;
    text-align: right;
}

.notes-section {
    background: #eff6ff;
    border-color: #dbeafe;
}

.notes-content {
    font-size: 0.875rem;
    color: #1e40af;
    line-height: 1.6;
    white-space: pre-wrap;
}

.financial-section {
    background: #ecfdf5;
    border-color: #d1fae5;
}
</style>
