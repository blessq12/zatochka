<template>
    <div class="works-section">
        <div class="section-header-block">
            <h2 class="section-title">
                {{
                    isRepairOrder
                        ? "Элементы оборудования и работы"
                        : "Позиции и выполненные работы"
                }}
            </h2>
            <p class="section-subtitle">
                {{
                    isRepairOrder
                        ? "Добавляй работы по элементу оборудования."
                        : "Добавляй работы прямо под нужной позицией заказа."
                }}
            </p>
        </div>

        <div v-if="repairableItems.length === 0" class="empty-state-inline">
            Нет позиций для фиксации работ
        </div>

        <template v-if="isRepairOrder">
            <div
                v-for="item in repairableItems"
                :key="item.id"
                class="position-work-card"
            >
                <div class="position-card-header">
                    <div class="position-card-title-group">
                        <h3 class="position-works-title">
                            {{ itemLabel(item) }}
                        </h3>
                    </div>
                    <button
                        v-if="
                            isWorkspaceEditable &&
                            item.repairable_quantity > 0
                        "
                        type="button"
                        class="btn-reject-item"
                        :disabled="isRejectingItem[item.id]"
                        @click="$emit('reject-item', item)"
                    >
                        <span v-if="isRejectingItem[item.id]">...</span>
                        <span v-else>Неремонтопригодно</span>
                    </button>
                </div>

                <div
                    v-if="!(item.components || []).length"
                    class="empty-state-inline compact"
                >
                    У оборудования нет элементов — добавьте их в карточке
                    оборудования.
                </div>

                <div
                    v-for="component in item.components || []"
                    :key="component.id"
                    class="component-work-block"
                >
                    <div class="component-work-header">
                        <h4 class="component-work-title">
                            {{ component.name }}
                        </h4>
                        <span
                            v-if="component.serial_number"
                            class="component-work-serial"
                        >
                            S/N: {{ component.serial_number }}
                        </span>
                    </div>

                    <div
                        v-if="worksForComponent(component.id).length === 0"
                        class="empty-state-inline compact"
                    >
                        Работы по элементу ещё не добавлены
                    </div>
                    <div v-else class="works-list">
                        <div
                            v-for="work in worksForComponent(component.id)"
                            :key="work.id"
                            class="work-item"
                        >
                            <div class="work-body">
                                <div class="work-content">
                                    <span class="work-description">{{
                                        work.description
                                    }}</span>
                                </div>
                                <button
                                    v-if="isWorkspaceEditable"
                                    @click="$emit('delete-work', work)"
                                    class="btn-delete btn-delete-inline"
                                    :disabled="isDeletingWork[work.id]"
                                >
                                    Удалить
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="isWorkspaceEditable"
                        class="position-work-form-block"
                    >
                        <label class="form-label"
                            >Добавить работу по элементу</label
                        >
                        <form
                            @submit.prevent="
                                $emit('add-work-for-component', component.id)
                            "
                            class="position-work-form"
                        >
                            <input
                                v-model="workDrafts[component.id]"
                                type="text"
                                class="form-input"
                                placeholder="Опишите выполненную работу"
                                required
                            />
                            <button
                                type="submit"
                                class="btn-primary position-work-submit"
                                :disabled="isAddingWorkForItem[component.id]"
                            >
                                <span
                                    v-if="isAddingWorkForItem[component.id]"
                                    >Сохранение...</span
                                >
                                <span v-else>Добавить работу</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <div
                v-for="group in worksByItem"
                :key="group.item.id"
                class="position-work-card"
            >
                <div class="position-card-header">
                    <div class="position-card-title-group">
                        <h3 class="position-works-title">
                            {{ itemLabel(group.item) }}
                        </h3>
                        <div class="position-card-meta">
                            <span class="position-works-qty">
                                К выдаче:
                                {{ group.item.repairable_quantity }}
                            </span>
                            <span
                                v-if="group.item.quantity != null"
                                class="position-card-meta-item"
                            >
                                Всего: {{ group.item.quantity }}
                            </span>
                            <span
                                v-if="group.item.rejected_quantity > 0"
                                class="position-card-meta-item warning"
                            >
                                Отклонено:
                                {{ group.item.rejected_quantity }}
                            </span>
                        </div>
                    </div>
                    <button
                        v-if="
                            isWorkspaceEditable &&
                            group.item.repairable_quantity > 0
                        "
                        type="button"
                        class="btn-reject-item"
                        :disabled="isRejectingItem[group.item.id]"
                        @click="$emit('reject-item', group.item)"
                    >
                        <span v-if="isRejectingItem[group.item.id]">...</span>
                        <span v-else>Неремонтопригодно</span>
                    </button>
                </div>

                <div
                    v-if="group.item.rejection_reason"
                    class="position-inline-note"
                >
                    Причина отклонения: {{ group.item.rejection_reason }}
                </div>

                <div
                    v-if="group.works.length === 0"
                    class="empty-state-inline compact"
                >
                    Работы по позиции ещё не добавлены
                </div>
                <div v-else class="works-list">
                    <div
                        v-for="work in group.works"
                        :key="work.id"
                        class="work-item"
                    >
                        <div class="work-body">
                            <div class="work-content">
                                <span class="work-description">{{
                                    work.description
                                }}</span>
                            </div>
                            <button
                                v-if="isWorkspaceEditable"
                                @click="$emit('delete-work', work)"
                                class="btn-delete btn-delete-inline"
                                :disabled="isDeletingWork[work.id]"
                            >
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="isWorkspaceEditable"
                    class="position-work-form-block"
                >
                    <label class="form-label"
                        >Добавить работу по этой позиции</label
                    >
                    <form
                        @submit.prevent="
                            $emit('add-work-for-item', group.item.id)
                        "
                        class="position-work-form"
                    >
                        <input
                            v-model="workDrafts[group.item.id]"
                            type="text"
                            class="form-input"
                            placeholder="Опишите выполненную работу"
                            required
                        />
                        <button
                            type="submit"
                            class="btn-primary position-work-submit"
                            :disabled="isAddingWorkForItem[group.item.id]"
                        >
                            <span
                                v-if="isAddingWorkForItem[group.item.id]"
                                >Сохранение...</span
                            >
                            <span v-else>Добавить работу</span>
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <div class="completion-panel">
            <div class="completion-summary">
                Всего работ: {{ worksCount }}
            </div>
            <div
                v-if="itemsWithoutWorks.length > 0"
                class="completion-hint"
            >
                Не хватает работ по {{ itemsWithoutWorks.length }}
                {{
                    itemsWithoutWorks.length === 1
                        ? isRepairOrder
                            ? "оборудованию"
                            : "позиции"
                        : isRepairOrder
                          ? "единицам оборудования"
                          : "позициям"
                }}.
            </div>
            <div v-else class="completion-hint success">
                {{
                    isRepairOrder
                        ? "По всему оборудованию есть выполненные работы."
                        : "По всем позициям есть выполненные работы."
                }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "OrderInWorkWorks",
    props: {
        isRepairOrder: { type: Boolean, required: true },
        repairableItems: { type: Array, default: () => [] },
        worksByItem: { type: Array, default: () => [] },
        itemsWithoutWorks: { type: Array, default: () => [] },
        worksCount: { type: Number, required: true },
        isWorkspaceEditable: { type: Boolean, required: true },
        workDrafts: { type: Object, required: true },
        isAddingWorkForItem: { type: Object, required: true },
        isDeletingWork: { type: Object, required: true },
        isRejectingItem: { type: Object, required: true },
        itemLabel: { type: Function, required: true },
        worksForComponent: { type: Function, required: true },
    },
    emits: [
        "reject-item",
        "delete-work",
        "add-work-for-component",
        "add-work-for-item",
    ],
};
</script>
