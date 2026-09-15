<template>
    <div class="semantic-stack">
        <div class="order-info-section">
            <div class="section-header-block">
                <h2 class="section-title">Контекст заказа</h2>
                <p class="section-subtitle">
                    Сводка по заказу и проблеме перед фиксацией работ.
                </p>
            </div>

            <div class="order-info-grid">
                <div class="info-card">
                    <div class="info-card-header">
                        <span class="info-card-title">Заказ</span>
                    </div>
                    <div class="info-card-content">
                        <div class="info-card-item">
                            <span class="info-card-label">Тип заказа</span>
                            <span class="info-card-value">{{
                                formatPosOrderPaymentType(order)
                            }}</span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        order.equipment?.name ||
                        order.equipment_name ||
                        equipmentBrandModelLine
                    "
                    class="info-card"
                >
                    <div class="info-card-header">
                        <span class="info-icon">⚙️</span>
                        <span class="info-card-title"
                            >Оборудование (ремонт)</span
                        >
                    </div>
                    <div class="info-card-content">
                        <div
                            v-if="equipmentBrandModelLine"
                            class="info-card-item"
                        >
                            <span class="info-card-label"
                                >Бренд / модель</span
                            >
                            <span class="info-card-value">{{
                                equipmentBrandModelLine
                            }}</span>
                        </div>
                        <div class="info-card-item">
                            <span class="info-card-label">Название</span>
                            <span class="info-card-value">
                                {{
                                    order.equipment?.name ||
                                    order.equipment_name
                                }}
                            </span>
                        </div>
                        <div
                            v-if="equipmentSerialRows.length > 0"
                            class="info-card-item"
                        >
                            <span class="info-card-label"
                                >Компоненты и серийные номера</span
                            >
                            <ul class="equipment-serial-list-work">
                                <li
                                    v-for="(row, idx) in equipmentSerialRows"
                                    :key="idx"
                                    class="info-card-value"
                                >
                                    <template
                                        v-if="row.name && row.serial_number"
                                    >
                                        {{ row.name }}:
                                        {{ row.serial_number }}
                                    </template>
                                    <template v-else-if="row.serial_number">{{
                                        row.serial_number
                                    }}</template>
                                    <template v-else>{{ row.name }}</template>
                                </li>
                            </ul>
                        </div>
                        <div
                            v-else-if="order.equipment?.serial_numbers?.length"
                            class="info-card-item"
                        >
                            <span class="info-card-label"
                                >Серийные номера</span
                            >
                            <span class="info-card-value">{{
                                order.equipment.serial_numbers.join(", ")
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="order.items && order.items.length > 0"
                class="info-card info-card-full"
            >
                <div class="info-card-header">
                    <span class="info-card-title">Позиции заказа</span>
                </div>
                <div class="info-card-content">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="order-item-row"
                    >
                        <div class="order-item-main">
                            <span class="info-card-value">
                                {{ itemLabel(item) }}
                            </span>
                            <span class="info-card-subvalue">
                                <template v-if="item.quantity != null">
                                    {{ item.repairable_quantity }}/{{
                                        item.quantity
                                    }}
                                    к работе
                                    <template
                                        v-if="item.rejected_quantity > 0"
                                    >
                                        · отклонено
                                        {{ item.rejected_quantity }}
                                    </template>
                                </template>
                                <template v-else>
                                    {{
                                        item.status === "rejected"
                                            ? "Неремонтопригодно"
                                            : "1 шт."
                                    }}
                                </template>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="order.problem_description" class="problem-card">
                <div class="problem-card-header">
                    <span class="problem-card-title">Описание проблемы</span>
                </div>
                <div class="problem-card-content">
                    {{ order.problem_description }}
                </div>
            </div>
        </div>

        <div class="support-grid">
            <div
                v-if="order.manager_rework_comment"
                class="rework-banner"
            >
                <div class="section-header-block">
                    <h2 class="section-title">Доработка от менеджера</h2>
                    <p class="section-subtitle">
                        Что нужно исправить или доделать по этому заказу.
                    </p>
                </div>
                <div class="rework-banner-content">
                    {{ order.manager_rework_comment }}
                </div>
            </div>

            <div class="comments-section support-card">
                <div class="section-header-block">
                    <h2 class="section-title">Комментарий мастера</h2>
                    <p class="section-subtitle">
                        Виден только внутри системы, клиент не увидит.
                    </p>
                </div>
                <div
                    v-if="masterInternalComments.length > 0"
                    class="comments-list"
                >
                    <div
                        v-for="comment in masterInternalComments"
                        :key="comment.id"
                        class="comment-item"
                    >
                        <div class="comment-content">
                            {{ comment.text }}
                        </div>
                    </div>
                </div>
                <div v-else class="empty-state-inline compact">
                    Комментарий ещё не добавлен
                </div>
                <form
                    v-if="isWorkspaceEditable"
                    @submit.prevent="$emit('save-comment')"
                    class="comment-form"
                >
                    <div class="form-group">
                        <label class="form-label">Добавить комментарий</label>
                        <textarea
                            v-model="commentForm.internal_notes"
                            class="form-textarea"
                            rows="3"
                            placeholder="Внутренний комментарий к задаче..."
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        class="btn-primary btn-save-comment"
                        :disabled="isSavingComment"
                    >
                        <span v-if="isSavingComment">Сохранение...</span>
                        <span v-else>Сохранить комментарий</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "OrderInWorkContext",
    props: {
        order: { type: Object, required: true },
        equipmentBrandModelLine: { type: String, default: "" },
        equipmentSerialRows: { type: Array, default: () => [] },
        masterInternalComments: { type: Array, default: () => [] },
        isWorkspaceEditable: { type: Boolean, required: true },
        isSavingComment: { type: Boolean, required: true },
        commentForm: { type: Object, required: true },
        formatPosOrderPaymentType: { type: Function, required: true },
        itemLabel: { type: Function, required: true },
    },
    emits: ["save-comment"],
};
</script>
