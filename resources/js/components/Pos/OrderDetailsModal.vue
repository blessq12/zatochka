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
                                    <span class="detail-label">Тип заказа</span>
                                    <span class="detail-value">{{
                                        formatPosOrderPaymentType(order)
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
                            v-if="
                                order.equipment?.name ||
                                order.equipment_name ||
                                equipmentBrandModelLine
                            "
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Оборудование (ремонт)
                                </h3>
                            </div>
                            <div class="details-grid">
                                <div
                                    v-if="equipmentBrandModelLine"
                                    class="detail-item"
                                >
                                    <span class="detail-label"
                                        >Бренд / модель</span
                                    >
                                    <span class="detail-value">{{
                                        equipmentBrandModelLine
                                    }}</span>
                                </div>
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
                                    v-if="equipmentSerialRows.length > 0"
                                    class="detail-item full-width"
                                >
                                    <span class="detail-label"
                                        >Компоненты и серийные номера</span
                                    >
                                    <ul class="equipment-serial-list-modal">
                                        <li
                                            v-for="(row, idx) in equipmentSerialRows"
                                            :key="idx"
                                            class="detail-value"
                                        >
                                            <template
                                                v-if="
                                                    row.name &&
                                                    row.serial_number
                                                "
                                            >
                                                {{ row.name }}:
                                                {{ row.serial_number }}
                                            </template>
                                            <template
                                                v-else-if="row.serial_number"
                                                >{{ row.serial_number }}</template
                                            >
                                            <template v-else>{{
                                                row.name
                                            }}</template>
                                        </li>
                                    </ul>
                                </div>
                                <div
                                    v-else-if="
                                        order.equipment?.serial_numbers_display
                                    "
                                    class="detail-item"
                                >
                                    <span class="detail-label"
                                        >Серийные номера</span
                                    >
                                    <span class="detail-value">{{
                                        order.equipment.serial_numbers_display
                                    }}</span>
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
                                        formatPosToolType(tool)
                                    }}</span>
                                    <span
                                        v-if="tool.name"
                                        class="tool-name"
                                        >{{ tool.name }}</span
                                    >
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
                            v-if="orderWorks.length > 0"
                            class="details-section"
                        >
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Выполненные работы
                                </h3>
                            </div>
                            <div class="works-list-details">
                                <div
                                    v-for="work in orderWorks"
                                    :key="work.id"
                                    class="work-detail-item"
                                >
                                    <p class="work-detail-description">
                                        {{ work.description }}
                                    </p>
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
                        <div v-if="order.master" class="details-section">
                            <div class="details-section-header">
                                <h3 class="details-section-title">
                                    Ответственные
                                </h3>
                            </div>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Мастер</span>
                                    <span class="detail-value">{{
                                        masterDisplayName
                                    }}</span>
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
import {
    formatPosOrderPaymentType,
    formatPosToolType,
    getEquipmentBrandModelLine,
    getEquipmentSerialRows,
} from "../../composables/usePosOrderDisplay.js";
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

        const equipmentSerialRows = computed(() =>
            getEquipmentSerialRows(order.value?.equipment)
        );

        const equipmentBrandModelLine = computed(() =>
            getEquipmentBrandModelLine(order.value?.equipment)
        );

        const orderWorks = computed(() => order.value?.works || []);

        const masterDisplayName = computed(() => {
            const master = order.value?.master;
            if (!master) {
                return "";
            }

            if (master.surname) {
                return `${master.surname} ${master.name}`;
            }

            return master.name || "";
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
            equipmentSerialRows,
            equipmentBrandModelLine,
            orderWorks,
            masterDisplayName,
            formatPosOrderPaymentType,
            formatPosToolType,
            close,
            formatDate,
            getStatusLabel: orderService.getStatusLabel,
            getStatusClass,
        };
    },
};
</script>

<style scoped src="./order-details-modal.css"></style>
