<template>
    <div class="order-card">
        <div class="order-card-content" @click="$emit('open-card', order.id)">
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
                        <span
                            v-if="order.needs_delivery"
                            class="delivery-badge"
                        >
                            Доставка
                        </span>
                    </div>
                </div>
                <div class="order-header-meta">
                    <span class="order-date">{{ dateLabel }}</span>
                    <span v-if="order.works_count > 0" class="works-count">
                        {{ order.works_count }}
                        {{ worksCountLabel }}
                    </span>
                </div>
            </div>

            <p v-if="order.subject_line" class="subject-line">
                {{ order.subject_line }}
            </p>

            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Услуга</span>
                    <span class="info-value">{{
                        order.service_type_label || "—"
                    }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Тип заказа</span>
                    <span class="info-value">{{
                        formatPosOrderPaymentType(order)
                    }}</span>
                </div>
                <div v-if="showClientName && order.client_name" class="info-row">
                    <span class="info-label">Клиент</span>
                    <span class="info-value">{{ order.client_name }}</span>
                </div>
                <div
                    v-if="equipmentSerialRows.length > 0"
                    class="equipment-serials-row"
                >
                    <span class="info-label">С/Н</span>
                    <ul class="equipment-serial-list">
                        <li
                            v-for="(row, idx) in equipmentSerialRows"
                            :key="idx"
                            class="equipment-serial-row"
                        >
                            <template v-if="row.name && row.serial_number">
                                {{ row.name }}: {{ row.serial_number }}
                            </template>
                            <template v-else-if="row.serial_number">{{
                                row.serial_number
                            }}</template>
                            <template v-else>{{ row.name }}</template>
                        </li>
                    </ul>
                </div>
                <div
                    v-if="
                        order.tools_summary &&
                        order.tools_summary.length > 0 &&
                        !order.equipment_summary
                    "
                    class="tools-row"
                >
                    <span
                        v-for="(tool, idx) in order.tools_summary"
                        :key="tool.tool_type + '-' + idx"
                        class="tool-badge"
                    >
                        {{ formatPosToolSummaryItem(tool) }}
                    </span>
                </div>
                <div
                    v-if="
                        order.problem_excerpt &&
                        order.subject_line !== order.problem_excerpt &&
                        !subjectIncludesProblem
                    "
                    class="problem-block"
                >
                    <p class="problem-text">{{ order.problem_excerpt }}</p>
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
import {
    formatPosOrderPaymentType,
    formatPosToolSummaryItem,
    getEquipmentSerialRows,
} from "../../composables/usePosOrderDisplay.js";
import { orderService } from "../../services/pos/OrderService.js";

export default {
    name: "OrderCard",
    props: {
        order: {
            type: Object,
            required: true,
        },
        showClientName: {
            type: Boolean,
            default: false,
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
    emits: ["open-card", "primary-action"],
    setup(props) {
        const hasActions = computed(() => {
            return props.primaryAction || !!props.$slots.actions;
        });

        const dateLabel = computed(() => {
            const raw = props.showClientName
                ? props.order.ready_at || props.order.created_at
                : props.order.created_at;

            return formatDateShort(raw);
        });

        const worksCountLabel = computed(() => {
            const count = props.order.works_count ?? 0;
            const mod10 = count % 10;
            const mod100 = count % 100;

            if (mod10 === 1 && mod100 !== 11) {
                return "работа";
            }
            if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) {
                return "работы";
            }

            return "работ";
        });

        const subjectIncludesProblem = computed(() => {
            const subject = props.order.subject_line || "";
            const problem = props.order.problem_excerpt || "";

            return problem !== "" && subject.includes(problem);
        });

        const equipmentSerialRows = computed(() =>
            getEquipmentSerialRows({
                serial_numbers: props.order.equipment_serial_numbers,
            })
        );

        const formatDateShort = (dateString) => {
            if (!dateString) {
                return "—";
            }

            const date = new Date(dateString);

            return new Intl.DateTimeFormat("ru-RU", {
                day: "2-digit",
                month: "2-digit",
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

        return {
            hasActions,
            dateLabel,
            worksCountLabel,
            subjectIncludesProblem,
            equipmentSerialRows,
            formatPosOrderPaymentType,
            formatPosToolSummaryItem,
            getStatusLabel: orderService.getStatusLabel,
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
    min-height: 220px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    font-family: "Jost", sans-serif;
}

.order-card-content {
    cursor: pointer;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.order-card:hover {
    box-shadow: 0 4px 16px rgba(0, 56, 89, 0.12);
    transform: translateY(-2px);
    border-color: rgba(0, 56, 89, 0.4);
}

.order-header {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
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
}

.order-header-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.order-date,
.works-count {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.order-header-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.order-status,
.urgency-badge,
.delivery-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 0;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-new {
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

.urgency-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.delivery-badge {
    background: #ede9fe;
    color: #5b21b6;
}

.subject-line {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    line-height: 1.45;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: flex-start;
}

.info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.info-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    text-align: right;
    word-break: break-word;
}

.equipment-serials-row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    align-items: flex-start;
}

.equipment-serial-list {
    margin: 0;
    padding: 0;
    list-style: none;
    text-align: right;
}

.equipment-serial-row {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    word-break: break-word;
}

.equipment-serial-row + .equipment-serial-row {
    margin-top: 0.25rem;
}

.tools-row {
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
    margin-top: 0.25rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
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
    margin-top: auto;
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
}

.btn-primary-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary-action.btn-status-in-work,
.btn-primary-action.btn-work {
    background: #003859;
}

@media (max-width: 768px) {
    .order-card {
        padding: 0.75rem;
        min-height: 200px;
    }

    .order-number {
        font-size: 0.9375rem;
    }

    .subject-line {
        font-size: 0.875rem;
    }

    .info-value {
        font-size: 0.8125rem;
    }

    .btn-primary-action {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }
}
</style>
