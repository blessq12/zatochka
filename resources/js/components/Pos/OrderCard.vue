<template>
    <div class="order-card">
        <div
            class="order-card-content"
            @click="$emit('view-details', order.id)"
        >
            <div class="order-header">
                <span class="order-number">{{ order.order_number }}</span>
                <div class="order-header-badges">
                    <span
                        class="order-status"
                        :class="getStatusClass(order.status)"
                    >
                        {{ getStatusLabel(order.status) }}
                    </span>
                    <span
                        v-if="order.urgency === 'urgent'"
                        class="urgency-badge urgent"
                    >
                        Срочно
                    </span>
                </div>
            </div>
            <div class="order-info">
                <div class="info-row">
                    <p>
                        <strong>Клиент:</strong> {{ order.client?.full_name }}
                    </p>
                    <p v-if="order.client?.phone">
                        <strong>Телефон:</strong> {{ order.client.phone }}
                    </p>
                </div>
                <div class="info-row">
                    <p>
                        <strong>Тип:</strong>
                        {{ getTypeLabel(order.service_type) }}
                    </p>
                    <p v-if="order.branch?.name">
                        <strong>Филиал:</strong> {{ order.branch.name }}
                    </p>
                </div>
                <div v-if="order.equipment_name" class="info-row">
                    <p>
                        <strong>Оборудование:</strong>
                        {{ order.equipment_name }}
                    </p>
                    <p v-if="order.equipment_serial_number">
                        <strong>Серийный №:</strong>
                        {{ order.equipment_serial_number }}
                    </p>
                </div>
                <div class="info-row">
                    <p>
                        <strong>Дата создания:</strong>
                        {{ formatDate(order.created_at) }}
                    </p>
                    <p
                        v-if="order.estimated_price || order.actual_price"
                        class="price-info"
                    >
                        <strong>Цена:</strong>
                        <span class="price-value"
                            >{{
                                formatPrice(
                                    order.actual_price || order.estimated_price
                                )
                            }}
                            ₽</span
                        >
                    </p>
                </div>
                <div v-if="order.problem_description" class="problem-preview">
                    <p><strong>Проблема:</strong></p>
                    <p class="problem-text">
                        {{ truncateText(order.problem_description, 100) }}
                    </p>
                </div>
            </div>
        </div>
        <div v-if="hasActions" class="order-actions">
            <slot name="actions">
                <button
                    v-if="primaryAction"
                    @click.stop="$emit('primary-action', order.id)"
                    :disabled="isLoading?.[order.id]"
                    class="btn-primary-action"
                    :class="primaryActionClass"
                >
                    <span v-if="isLoading?.[order.id]">{{
                        primaryActionLoadingText
                    }}</span>
                    <span v-else>{{ primaryActionText }}</span>
                </button>
            </slot>
        </div>
    </div>
</template>

<script>
import { computed } from "vue";
import { orderService } from "../../services/pos/OrderService.js";

export default {
    name: "OrderCard",
    props: {
        order: {
            type: Object,
            required: true,
        },
        primaryAction: {
            type: Boolean,
            default: false,
        },
        primaryActionText: {
            type: String,
            default: "Выполнить действие",
        },
        primaryActionLoadingText: {
            type: String,
            default: "Сохранение...",
        },
        primaryActionClass: {
            type: String,
            default: "",
        },
        isLoading: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ["view-details", "primary-action"],
    setup(props) {
        const hasActions = computed(() => {
            return props.primaryAction || !!props.$slots.actions;
        });

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

        const truncateText = (text, maxLength) => {
            if (!text) return "";
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + "...";
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

        return {
            hasActions,
            formatDate,
            truncateText,
            getStatusLabel: orderService.getStatusLabel,
            getTypeLabel: orderService.getTypeLabel,
            formatPrice: orderService.formatPrice,
            getStatusClass,
        };
    },
};
</script>

<style scoped>
.order-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-card-content {
    cursor: pointer;
    flex: 1;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
    gap: 1rem;
}

.order-number {
    font-weight: 700;
    font-size: 1.125rem;
    color: #046490;
    flex-shrink: 0;
}

.order-header-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.order-status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-new,
.status-consultation,
.status-diagnostic {
    background: #dbeafe;
    color: #1e40af;
}

.status-in-work,
.status-waiting-parts {
    background: #fef3c7;
    color: #92400e;
}

.status-ready {
    background: #d1fae5;
    color: #065f46;
}

.status-issued {
    background: #dbeafe;
    color: #1e40af;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.urgency-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.urgency-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-row {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-row p {
    margin: 0;
    color: #374151;
    font-size: 0.875rem;
    display: flex;
    align-items: flex-start;
}

.price-info {
    margin-top: 0.5rem;
}

.price-value {
    color: #046490;
    font-weight: 600;
    margin-left: 0.25rem;
}

.problem-preview {
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.problem-preview p {
    margin: 0.25rem 0;
    color: #374151;
    font-size: 0.875rem;
}

.problem-text {
    color: #6b7280;
    font-size: 0.8125rem;
    line-height: 1.4;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.btn-primary-action {
    width: 100%;
    padding: 0.75rem 1rem;
    background: #046490;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary-action:hover:not(:disabled) {
    background: #003859;
    transform: translateY(-1px);
}

.btn-primary-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Специфичные классы для разных типов действий */
.btn-primary-action.btn-status-in-work {
    background: #046490;
}

.btn-primary-action.btn-status-in-work:hover:not(:disabled) {
    background: #003859;
}

.btn-primary-action.btn-work {
    background: #046490;
}

.btn-primary-action.btn-work:hover:not(:disabled) {
    background: #003859;
}

.btn-primary-action.btn-return-to-work {
    background: #f59e0b;
}

.btn-primary-action.btn-return-to-work:hover:not(:disabled) {
    background: #d97706;
}
</style>
