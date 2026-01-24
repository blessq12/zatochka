<template>
    <div class="order-card">
        <div
            class="order-card-content"
            @click="$emit('view-details', order.id)"
        >
            <!-- Заголовок с номером и статусами -->
            <div class="order-header">
                <div class="order-header-main">
                    <span class="order-number">№{{ order.order_number }}</span>
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
                <div class="order-header-meta">
                    <span class="order-date">{{ formatDateShort(order.created_at) }}</span>
                    <span v-if="order.estimated_price || order.actual_price" class="order-price">
                        {{ formatPrice(order.actual_price || order.estimated_price) }} ₽
                    </span>
                </div>
            </div>

            <!-- Основная информация -->
            <div class="order-info">
                <!-- Клиент -->
                <div class="info-block">
                    <div class="info-item">
                        <div class="info-content">
                            <span class="info-label">Клиент</span>
                            <span class="info-value">{{ order.client?.full_name || "—" }}</span>
                        </div>
                    </div>
                    <div v-if="order.client?.phone" class="info-item">
                        <div class="info-content">
                            <span class="info-label">Телефон</span>
                            <a :href="`tel:${order.client.phone}`" class="info-value link" @click.stop>
                                {{ order.client.phone }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Тип услуги и филиал -->
                <div class="info-block">
                    <div class="info-item">
                        <div class="info-content">
                            <span class="info-label">Тип услуги</span>
                            <span class="info-value">{{ getTypeLabel(order.service_type) }}</span>
                        </div>
                    </div>
                    <div v-if="order.branch?.name" class="info-item">
                        <div class="info-content">
                            <span class="info-label">Филиал</span>
                            <span class="info-value">{{ order.branch.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Оборудование (если есть) -->
                <div v-if="order.equipment?.name || order.equipment_name" class="info-block">
                    <div class="info-item">
                        <div class="info-content">
                            <span class="info-label">Оборудование</span>
                            <span class="info-value">
                                {{ order.equipment?.name || order.equipment_name }}
                            </span>
                            <span v-if="order.equipment?.serial_number || order.equipment_serial_number" class="info-subvalue">
                                С/Н: {{ order.equipment?.serial_number || order.equipment_serial_number }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Инструменты для заточки (если есть) -->
                <div v-if="order.tools && order.tools.length > 0" class="info-block">
                    <div class="info-item">
                        <div class="info-content">
                            <span class="info-label">Инструменты</span>
                            <div class="tools-list">
                                <span 
                                    v-for="(tool, idx) in order.tools" 
                                    :key="tool.id || idx"
                                    class="tool-badge"
                                >
                                    {{ tool.tool_type }} ({{ tool.quantity }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Описание проблемы -->
                <div v-if="order.problem_description" class="problem-block">
                    <span class="problem-label">Описание проблемы:</span>
                    <p class="problem-text">
                        {{ truncateText(order.problem_description, 120) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Действия -->
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

        const formatDateShort = (dateString) => {
            if (!dateString) return "—";
            const date = new Date(dateString);
            return new Intl.DateTimeFormat("ru-RU", {
                day: "2-digit",
                month: "2-digit",
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
            formatDateShort,
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
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(0, 56, 89, 0.25);
    border-radius: 0;
    padding: 1.25rem;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    font-family: "Jost", sans-serif;
}

.order-card-content {
    cursor: pointer;
    flex: 1;
}

.order-card:hover {
    box-shadow: 0 4px 16px rgba(0, 56, 89, 0.12);
    transform: translateY(-2px);
    border-color: rgba(0, 56, 89, 0.4);
}

.order-header {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.order-header-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.order-number {
    font-weight: 700;
    font-size: 1.125rem;
    color: #003859;
    flex-shrink: 0;
    font-family: "Jost", sans-serif;
}

.order-header-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.order-date {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.order-price {
    font-weight: 700;
    color: #059669;
    font-size: 0.9375rem;
}

.order-header-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.order-status {
    padding: 0.25rem 0.75rem;
    border-radius: 0;
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
    border-radius: 0;
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
    gap: 1rem;
}

.info-block {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.info-icon {
    font-size: 1.125rem;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.info-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    word-break: break-word;
}

.info-value.link {
    color: #003859;
    text-decoration: none;
}

.info-value.link:hover {
    text-decoration: underline;
}

.info-subvalue {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.125rem;
}

.tools-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.tool-badge {
    padding: 0.25rem 0.5rem;
    background: rgba(0, 56, 89, 0.08);
    border: 1px solid rgba(0, 56, 89, 0.2);
    border-radius: 0;
    font-size: 0.75rem;
    font-weight: 500;
    color: #003859;
}

.problem-block {
    margin-top: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.problem-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.problem-text {
    color: #374151;
    font-size: 0.8125rem;
    line-height: 1.5;
    margin: 0;
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
    background: #003859;
    color: white;
    border: none;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 700;
    font-family: "Jost", sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0, 56, 89, 0.25);
}

.btn-primary-action:hover:not(:disabled) {
    background: #002c4e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 56, 89, 0.3);
}

.btn-primary-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary-action.btn-status-in-work,
.btn-primary-action.btn-work {
    background: #003859;
}

.btn-primary-action.btn-status-in-work:hover:not(:disabled),
.btn-primary-action.btn-work:hover:not(:disabled) {
    background: #002c4e;
}

.btn-primary-action.btn-return-to-work {
    background: #c20a6c;
    box-shadow: 0 2px 8px rgba(194, 10, 108, 0.25);
}

.btn-primary-action.btn-return-to-work:hover:not(:disabled) {
    background: #a8095a;
    box-shadow: 0 4px 12px rgba(194, 10, 108, 0.3);
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .order-card {
        padding: 0.75rem;
        border-radius: 0;
    }

    .order-header {
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .order-header-badges {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .order-number {
        font-size: 0.9375rem;
    }

    .order-status,
    .urgency-badge {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
    }

    .order-info {
        gap: 0.5rem;
    }

    .info-row {
        gap: 0.25rem;
    }

    .info-row p {
        font-size: 0.8125rem;
        flex-direction: column;
        gap: 0.125rem;
        line-height: 1.4;
    }

    .info-row p strong {
        display: block;
        margin-bottom: 0.125rem;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .problem-preview {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }

    .problem-preview p {
        font-size: 0.75rem;
    }

    .problem-text {
        font-size: 0.6875rem;
    }

    .order-actions {
        padding-top: 0.75rem;
    }

    .btn-primary-action {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }
}
</style>
