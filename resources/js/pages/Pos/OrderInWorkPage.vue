<template>
    <div class="pos-page-content order-in-work-page">
        <div v-if="isLoading" class="loading">Загрузка...</div>
        <div v-else-if="!order" class="error-state">
            <p>Заказ не найден</p>
            <router-link :to="{ name: backRouteName }" class="btn-back">
                Вернуться к списку
            </router-link>
        </div>
        <div v-else class="order-content">
            <!-- Заголовок с кнопками действий -->
            <div class="order-header-section">
                <div class="order-header-top">
                    <div class="order-title-group">
                        <h1 class="order-title">
                            Заказ №{{ order.order_number }}
                        </h1>
                        <div class="order-status-group">
                            <span
                                class="order-status-badge"
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
                    <router-link
                        :to="{ name: backRouteName }"
                        class="btn-back"
                    >
                        ← Назад
                    </router-link>
                </div>

                <!-- Кнопки изменения статуса -->
                <div v-if="!isReadOnly" class="status-actions">
                    <button
                        @click="setInWorkStatus"
                        class="btn-status btn-in-work"
                        :disabled="
                            isChangingStatus || order.status === 'in_work'
                        "
                    >
                        <span
                            v-if="
                                isChangingStatus &&
                                changingToStatus === 'in_work'
                            "
                            >Сохранение...</span
                        >
                        <span v-else>{{ inWorkButtonLabel }}</span>
                    </button>
                    <button
                        @click="setWaitingPartsStatus"
                        class="btn-status btn-waiting-parts"
                        :disabled="
                            isChangingStatus ||
                            order.status !== 'in_work'
                        "
                    >
                        <span
                            v-if="
                                isChangingStatus &&
                                changingToStatus === 'waiting_parts'
                            "
                            >Сохранение...</span
                        >
                        <span v-else>Ожидание запчастей</span>
                    </button>
                    <button
                        @click="completeOrder"
                        class="btn-status btn-complete"
                        :disabled="
                            isCompletingOrder ||
                            order.status !== 'in_work' ||
                            works.length === 0
                        "
                        :title="completeButtonTitle"
                    >
                        <span v-if="isCompletingOrder">Сохранение...</span>
                        <span v-else>Завершить заказ</span>
                    </button>
                </div>
                <p v-else class="readonly-hint">
                    Заказ готов к выдаче — только просмотр. Изменения через менеджера.
                </p>
                <p
                    v-if="order.status === 'waiting_parts'"
                    class="waiting-parts-hint"
                >
                    Заказ ожидает запчасти. Переведите в работу, чтобы добавить
                    работы и комментарии.
                </p>
            </div>

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
                                equipmentBrandModelLineComputed
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
                                    v-if="equipmentBrandModelLineComputed"
                                    class="info-card-item"
                                >
                                    <span class="info-card-label"
                                        >Бренд / модель</span
                                    >
                                    <span class="info-card-value">{{
                                        equipmentBrandModelLineComputed
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
                                    v-if="equipmentSerialRowsComputed.length > 0"
                                    class="info-card-item"
                                >
                                    <span class="info-card-label"
                                        >Компоненты и серийные номера</span
                                    >
                                    <ul class="equipment-serial-list-work">
                                        <li
                                            v-for="(row, idx) in equipmentSerialRowsComputed"
                                            :key="idx"
                                            class="info-card-value"
                                        >
                                            <template
                                                v-if="row.name && row.serial_number"
                                            >
                                                {{ row.name }}:
                                                {{ row.serial_number }}
                                            </template>
                                            <template
                                                v-else-if="row.serial_number"
                                                >{{ row.serial_number }}</template
                                            >
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
                            <span class="problem-card-title"
                                >Описание проблемы</span
                            >
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
                            @submit.prevent="saveComment"
                            class="comment-form"
                        >
                            <div class="form-group">
                                <label class="form-label"
                                    >Добавить комментарий</label
                                >
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

                <div
                    v-if="repairableItems.length === 0"
                    class="empty-state-inline"
                >
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
                                @click="rejectItem(item)"
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
                                v-if="
                                    worksForComponent(component.id).length === 0
                                "
                                class="empty-state-inline compact"
                            >
                                Работы по элементу ещё не добавлены
                            </div>
                            <div v-else class="works-list">
                                <div
                                    v-for="work in worksForComponent(
                                        component.id
                                    )"
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
                                            @click="deleteWork(work)"
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
                                        addWorkForComponent(component.id)
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
                                        :disabled="
                                            isAddingWorkForItem[component.id]
                                        "
                                    >
                                        <span
                                            v-if="
                                                isAddingWorkForItem[
                                                    component.id
                                                ]
                                            "
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
                                @click="rejectItem(group.item)"
                            >
                                <span v-if="isRejectingItem[group.item.id]"
                                    >...</span
                                >
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
                                        @click="deleteWork(work)"
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
                                @submit.prevent="addWorkForItem(group.item.id)"
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
                                    :disabled="
                                        isAddingWorkForItem[group.item.id]
                                    "
                                >
                                    <span
                                        v-if="
                                            isAddingWorkForItem[group.item.id]
                                        "
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
                        Всего работ: {{ works.length }}
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
        </div>
    </div>
</template>

<script>
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    formatPosOrderPaymentType,
    getEquipmentBrandModelLine,
    getEquipmentSerialRows,
} from "../../composables/usePosOrderDisplay.js";
import {
    orderListRouteNameForStatus,
    POS_ORDER_TABS,
} from "../../composables/usePosOrderTabs.js";
import { orderService } from "../../services/pos/OrderService.js";
import { toastService } from "../../services/toastService.js";
import { usePosStore } from "../../stores/posStore.js";

export default {
    name: "OrderInWorkPage",
    setup() {
        const route = useRoute();
        const router = useRouter();
        const orderId = computed(() => route.params.id);

        const order = ref(null);
        const isLoading = ref(false);
        const works = ref([]);
        const workDrafts = reactive({});
        const isAddingWorkForItem = reactive({});
        const isDeletingWork = reactive({});
        const isRejectingItem = reactive({});
        const isCompletingOrder = ref(false);
        const isChangingStatus = ref(false);
        const changingToStatus = ref(null);
        const isSavingComment = ref(false);

        const commentForm = reactive({
            internal_notes: "",
        });

        const syncOrderDetails = (orderData) => {
            order.value = orderData;
            works.value = orderData?.works || [];
            ensureWorkDrafts();
        };

        const isRepairOrder = computed(
            () => order.value?.service_type === "repair"
        );

        const repairableItems = computed(() =>
            (order.value?.items || []).filter(
                (item) =>
                    (item.repairable_quantity ?? 1) > 0 &&
                    item.status !== "rejected"
            )
        );

        const masterInternalComments = computed(
            () => order.value?.master_internal_comments || []
        );

        const worksByItem = computed(() =>
            repairableItems.value.map((item) => ({
                item,
                works: works.value.filter(
                    (work) => work.order_item_id === item.id
                ),
            }))
        );

        const workDraftTargets = computed(() => {
            if (isRepairOrder.value) {
                return repairableItems.value.flatMap((item) =>
                    (item.components || []).map((component) => component.id)
                );
            }

            return repairableItems.value.map((item) => item.id);
        });

        const itemsWithoutWorks = computed(() =>
            repairableItems.value.filter(
                (item) =>
                    !works.value.some((work) => work.order_item_id === item.id)
            )
        );

        const worksForComponent = (componentId) =>
            works.value.filter(
                (work) => work.equipment_component_id === componentId
            );

        const ensureWorkDrafts = () => {
            const activeIds = new Set(workDraftTargets.value);

            workDraftTargets.value.forEach((targetId) => {
                if (typeof workDrafts[targetId] !== "string") {
                    workDrafts[targetId] = "";
                }

                if (typeof isAddingWorkForItem[targetId] !== "boolean") {
                    isAddingWorkForItem[targetId] = false;
                }
            });

            Object.keys(workDrafts).forEach((key) => {
                const targetId = Number(key);
                if (!activeIds.has(targetId)) {
                    delete workDrafts[key];
                }
            });

            Object.keys(isAddingWorkForItem).forEach((key) => {
                const targetId = Number(key);
                if (!activeIds.has(targetId)) {
                    delete isAddingWorkForItem[key];
                }
            });
        };

        const addWorkForItem = async (itemId) => {
            const description = (workDrafts[itemId] || "").trim();

            if (!description) {
                toastService.error("Опишите выполненную работу");
                return;
            }

            isAddingWorkForItem[itemId] = true;
            try {
                const orderData = await orderService.addWork(
                    orderId.value,
                    description,
                    { orderItemId: itemId }
                );
                syncOrderDetails(orderData);
                toastService.success("Работа добавлена");
                workDrafts[itemId] = "";
            } catch (error) {
                console.error("Error adding work:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при добавлении работы"
                );
            } finally {
                isAddingWorkForItem[itemId] = false;
            }
        };

        const addWorkForComponent = async (componentId) => {
            const description = (workDrafts[componentId] || "").trim();

            if (!description) {
                toastService.error("Опишите выполненную работу");
                return;
            }

            isAddingWorkForItem[componentId] = true;
            try {
                const orderData = await orderService.addWork(
                    orderId.value,
                    description,
                    { equipmentComponentId: componentId }
                );
                syncOrderDetails(orderData);
                toastService.success("Работа добавлена");
                workDrafts[componentId] = "";
            } catch (error) {
                console.error("Error adding work:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при добавлении работы"
                );
            } finally {
                isAddingWorkForItem[componentId] = false;
            }
        };

        const fetchOrder = async () => {
            isLoading.value = true;
            try {
                const orderData = await orderService.getOrderById(
                    orderId.value
                );
                syncOrderDetails(orderData);
            } catch (error) {
                console.error("Error fetching order:", error);
                toastService.error("Ошибка при загрузке заказа");
            } finally {
                isLoading.value = false;
            }
        };

        const itemLabel = (item) => {
            if (item.tool_name) {
                return item.tool_name;
            }

            if (item.client_equipment_id) {
                const equipmentList = order.value?.equipment_list || [];
                const equipment =
                    equipmentList.find(
                        (entry) => entry.id === item.client_equipment_id
                    ) ||
                    (order.value?.equipment?.id === item.client_equipment_id
                        ? order.value.equipment
                        : null);

                if (equipment) {
                    const label = [equipment.brand, equipment.model]
                        .filter(Boolean)
                        .join(" ");

                    if (label) {
                        return label;
                    }

                    if (equipment.name) {
                        return equipment.name;
                    }
                }

                return `Оборудование #${item.client_equipment_id}`;
            }

            return `#${item.id}`;
        };

        const rejectItem = async (item) => {
            const isSharpening = item.quantity != null;
            let quantity = 1;

            if (isSharpening) {
                const maxQty = item.repairable_quantity || item.quantity || 1;
                const input = prompt(
                    `Сколько единиц неремонтопригодно? (макс. ${maxQty})`,
                    "1"
                );
                if (input === null) {
                    return;
                }
                quantity = parseInt(input, 10);
                if (!Number.isFinite(quantity) || quantity < 1) {
                    toastService.error("Укажите корректное количество");
                    return;
                }
            } else if (
                !confirm("Отметить оборудование как неремонтопригодное?")
            ) {
                return;
            }

            const reason = prompt("Причина (неремонтопригодно):");
            if (reason === null) {
                return;
            }
            if (!reason.trim()) {
                toastService.error("Укажите причину");
                return;
            }

            isRejectingItem[item.id] = true;
            try {
                const orderData = await orderService.rejectItem(
                    orderId.value,
                    item.id,
                    reason.trim(),
                    quantity
                );
                syncOrderDetails(orderData);
                toastService.success("Позиция обновлена");
            } catch (error) {
                console.error("Error rejecting item:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при отклонении позиции"
                );
            } finally {
                isRejectingItem[item.id] = false;
            }
        };

        const deleteWork = async (work) => {
            if (!confirm("Удалить эту работу?")) return;

            isDeletingWork[work.id] = true;
            try {
                const orderData = await orderService.removeWork(
                    orderId.value,
                    work.sort_order
                );
                syncOrderDetails(orderData);
                toastService.success("Работа удалена");
            } catch (error) {
                console.error("Error deleting work:", error);
                toastService.error("Ошибка при удалении работы");
            } finally {
                isDeletingWork[work.id] = false;
            }
        };

        const setInWorkStatus = async () => {
            const status = order.value?.status;
            const confirmMessage =
                status === "new"
                    ? "Взять заказ в работу?"
                    : "Перевести заказ в статус «В работе»?";

            if (!confirm(confirmMessage)) {
                return;
            }

            isChangingStatus.value = true;
            changingToStatus.value = "in_work";
            try {
                const orderData =
                    status === "new"
                        ? await orderService.takeToWork(orderId.value)
                        : await orderService.resume(orderId.value);
                syncOrderDetails(orderData);
                toastService.success(
                    status === "new"
                        ? "Заказ взят в работу"
                        : "Заказ переведен в работу"
                );

                const posStore = usePosStore();
                await posStore.getOrdersCount();
            } catch (error) {
                console.error("Error updating order status:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при изменении статуса заказа"
                );
            } finally {
                isChangingStatus.value = false;
                changingToStatus.value = null;
            }
        };

        const setWaitingPartsStatus = async () => {
            if (!confirm("Перевести заказ в статус 'Ожидание запчастей'?")) {
                return;
            }

            isChangingStatus.value = true;
            changingToStatus.value = "waiting_parts";
            try {
                const orderData = await orderService.markWaitingForParts(
                    orderId.value
                );
                syncOrderDetails(orderData);
                toastService.success("Заказ переведен в ожидание запчастей");

                const posStore = usePosStore();
                await posStore.getOrdersCount();
            } catch (error) {
                console.error("Error updating order status:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при изменении статуса заказа"
                );
            } finally {
                isChangingStatus.value = false;
                changingToStatus.value = null;
            }
        };

        const saveComment = async () => {
            const newNote = commentForm.internal_notes.trim();
            if (!newNote) {
                return;
            }

            isSavingComment.value = true;
            try {
                const orderData = await orderService.updateInternalNotes(
                    orderId.value,
                    newNote
                );
                syncOrderDetails(orderData);
                toastService.success("Комментарий сохранен");
                commentForm.internal_notes = "";
            } catch (error) {
                console.error("Error saving comment:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при сохранении комментария"
                );
            } finally {
                isSavingComment.value = false;
            }
        };

        const completeOrder = async () => {
            if (order.value?.status !== "in_work") {
                toastService.error(
                    "Завершить можно только заказ в статусе «В работе»."
                );
                return;
            }

            if (works.value.length === 0) {
                toastService.error(
                    "Нельзя завершить заказ без выполненных работ. Добавьте работы по позициям."
                );
                return;
            }

            if (itemsWithoutWorks.length > 0) {
                toastService.error(
                    "Укажите выполненные работы по каждой позиции заказа"
                );
                return;
            }

            if (
                !confirm(
                    "Завершить работу по заказу? Заказ уйдёт менеджеру на оценку стоимости."
                )
            ) {
                return;
            }

            isCompletingOrder.value = true;
            try {
                const orderData = await orderService.markReady(orderId.value);
                syncOrderDetails(orderData);
                toastService.success(
                    "Работа завершена. Заказ передан на оценку."
                );

                const posStore = usePosStore();
                await posStore.getOrdersCount();

                setTimeout(() => {
                    router.push({ name: "pos.orders.ready" });
                }, 1500);
            } catch (error) {
                console.error("Error completing order:", error);
                toastService.error(
                    error.response?.data?.message ||
                        "Ошибка при завершении заказа"
                );
            } finally {
                isCompletingOrder.value = false;
            }
        };

        const equipmentSerialRowsComputed = computed(() =>
            getEquipmentSerialRows(order.value?.equipment)
        );

        const equipmentBrandModelLineComputed = computed(() =>
            getEquipmentBrandModelLine(order.value?.equipment)
        );

        const isReadOnly = computed(() => {
            const status = order.value?.status;
            return status === "ready" || status === "issued" || status === "cancelled";
        });

        const isWorkspaceEditable = computed(
            () => order.value?.status === "in_work"
        );

        const inWorkButtonLabel = computed(() => {
            switch (order.value?.status) {
                case "new":
                    return "Взять в работу";
                case "waiting_parts":
                    return "Вернуть в работу";
                default:
                    return "В работе";
            }
        });

        const completeButtonTitle = computed(() => {
            if (order.value?.status === "waiting_parts") {
                return "Сначала переведите заказ в работу";
            }

            if (works.value.length === 0) {
                return "Нельзя завершить заказ без выполненных работ";
            }

            if (itemsWithoutWorks.length > 0) {
                return "Укажите работы по каждой позиции";
            }

            return "";
        });

        const backRouteName = computed(() => {
            if (order.value?.status) {
                return orderListRouteNameForStatus(order.value.status);
            }

            return POS_ORDER_TABS.in_work.routeName;
        });

        onMounted(async () => {
            await fetchOrder();
        });

        return {
            order,
            isLoading,
            works,
            workDrafts,
            repairableItems,
            masterInternalComments,
            worksByItem,
            itemsWithoutWorks,
            isRepairOrder,
            equipmentSerialRowsComputed,
            equipmentBrandModelLineComputed,
            formatPosOrderPaymentType,
            isReadOnly,
            isWorkspaceEditable,
            inWorkButtonLabel,
            completeButtonTitle,
            backRouteName,
            isAddingWorkForItem,
            isDeletingWork,
            isRejectingItem,
            isCompletingOrder,
            isChangingStatus,
            changingToStatus,
            isSavingComment,
            commentForm,
            setInWorkStatus,
            setWaitingPartsStatus,
            saveComment,
            completeOrder,
            itemLabel,
            worksForComponent,
            addWorkForItem,
            addWorkForComponent,
            rejectItem,
            deleteWork,
            getStatusLabel: orderService.getStatusLabel,
            formatPrice: orderService.formatPrice,
            getStatusClass: (status) => {
                const classes = {
                    new: "status-new",
                    in_work: "status-in-work",
                    waiting_parts: "status-waiting-parts",
                    ready: "status-ready",
                    issued: "status-issued",
                    cancelled: "status-cancelled",
                };
                return classes[status] || "";
            },
        };
    },
};
</script>

<style scoped>
.order-in-work-page {
    max-width: 1200px;
}

.order-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Заголовок заказа */
.order-header-section {
    background: white;
    border-radius: 0;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.order-header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.order-title-group {
    flex: 1;
    min-width: 0;
}

.order-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #003859;
    margin: 0 0 0.75rem 0;
    font-family: "Jost", sans-serif;
}

.order-status-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.order-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0;
    font-size: 0.8125rem;
    font-weight: 600;
    white-space: nowrap;
}

.order-status-badge.status-new {
    background: #dbeafe;
    color: #1e40af;
}

.order-status-badge.status-in-work,
.order-status-badge.status-waiting-parts {
    background: #fef3c7;
    color: #92400e;
}

.order-status-badge.status-ready {
    background: #d1fae5;
    color: #065f46;
}

.order-status-badge.status-issued {
    background: #dbeafe;
    color: #1e40af;
}

.order-status-badge.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.urgency-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.urgency-badge.urgent {
    background: #fee2e2;
    color: #991b1b;
}

.status-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.readonly-hint {
    margin: 0;
    padding: 0.75rem 1rem;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    font-size: 0.875rem;
    font-weight: 500;
}

.waiting-parts-hint {
    margin: 0.75rem 0 0;
    padding: 0.75rem 1rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
    font-size: 0.875rem;
    font-weight: 500;
}

.semantic-stack {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.support-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.section-header-block {
    margin-bottom: 1rem;
}

.section-subtitle {
    margin: 0.4rem 0 0;
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.5;
}

.support-card,
.order-info-section,
.rework-banner {
    background: white;
    border-radius: 0;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-status {
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Jost", sans-serif;
}

.btn-status:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-status:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-status.btn-in-work {
    background: #3b82f6;
    color: white;
}

.btn-status.btn-in-work:hover:not(:disabled) {
    background: #2563eb;
}

.btn-status.btn-in-work:disabled {
    background: #9ca3af;
}

.btn-status.btn-waiting-parts {
    background: #f59e0b;
    color: white;
}

.btn-status.btn-waiting-parts:hover:not(:disabled) {
    background: #d97706;
}

.btn-status.btn-waiting-parts:disabled {
    background: #9ca3af;
}

.btn-status.btn-complete {
    background: #059669;
    color: white;
}

.btn-status.btn-complete:hover:not(:disabled) {
    background: #047857;
}

.btn-status.btn-complete:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-status.btn-complete:disabled[title] {
    position: relative;
}

.btn-status.btn-complete:disabled[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.5rem 0.75rem;
    background: #1f2937;
    color: white;
    border-radius: 0;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-status.btn-complete:disabled[title]:hover::before {
    content: "";
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #1f2937;
    margin-bottom: -0.5rem;
    z-index: 1000;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #003859;
    margin: 0;
    font-family: "Jost", sans-serif;
}

.btn-back {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    border-radius: 0;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #e5e7eb;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0;
    padding: 1rem;
    transition: all 0.2s;
}

.info-card:hover {
    border-color: #003859;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.info-icon {
    font-size: 1.125rem;
}

.info-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #003859;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-card-full {
    grid-column: 1 / -1;
}

.order-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.order-item-row:last-child {
    border-bottom: none;
}

.order-item-main {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.btn-reject-item {
    flex-shrink: 0;
    border: 1px solid #fca5a5;
    background: #fef2f2;
    color: #b91c1c;
    border-radius: 0.5rem;
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-reject-item:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.info-card-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-card-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    word-break: break-word;
}

.info-card-value.link {
    color: #046490;
    text-decoration: none;
}

.info-card-value.link:hover {
    text-decoration: underline;
}

.info-card-subvalue {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.125rem;
}

.problem-card {
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 0;
    padding: 1rem;
    margin-top: 1rem;
}

.problem-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.problem-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.problem-card-content {
    font-size: 0.875rem;
    color: #78350f;
    line-height: 1.6;
    white-space: pre-wrap;
}

.works-section {
    background: white;
    border-radius: 0;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.equipment-serial-list-work {
    margin: 0.25rem 0 0;
    padding-left: 1.25rem;
    list-style: disc;
}

.equipment-serial-list-work li {
    margin-bottom: 0.25rem;
}

.position-work-card {
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.position-work-card:last-of-type {
    margin-bottom: 0;
}

.component-work-block {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.component-work-block:first-of-type {
    margin-top: 0.5rem;
}

.component-work-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.component-work-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #003859;
    font-family: "Jost", sans-serif;
}

.component-work-serial {
    font-size: 0.8125rem;
    color: #6b7280;
}

.position-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.position-card-title-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 0;
}

.position-card-meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.position-card-meta-item {
    font-size: 0.8125rem;
    color: #6b7280;
}

.position-card-meta-item.warning {
    color: #b45309;
    font-weight: 600;
}

.position-works-title {
    margin: 0 0 0.75rem;
    font-size: 1rem;
    font-weight: 600;
    color: #003859;
}

.position-works-qty {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #6b7280;
}

.position-inline-note {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: #fff7ed;
    border: 1px solid #fdba74;
    color: #9a3412;
    font-size: 0.875rem;
}

.works-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.work-item {
    border: 1px solid #e5e7eb;
    border-radius: 0;
    padding: 1rem;
    background: #f9fafb;
    transition: all 0.2s;
}

.work-item:hover {
    border-color: #003859;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.work-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.work-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
}

.section-hint {
    margin: 0 0 0.75rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.work-position {
    font-size: 0.75rem;
    font-weight: 600;
    color: #046490;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.work-description {
    color: #374151;
    line-height: 1.5;
    font-size: 0.9375rem;
}

.btn-delete-inline {
    flex-shrink: 0;
}

.empty-state-inline {
    color: #6b7280;
    font-size: 0.9375rem;
    padding: 1.5rem;
    text-align: center;
    background: #f9fafb;
    border: 1px dashed #e5e7eb;
    border-radius: 0;
    margin-bottom: 1.5rem;
}

.empty-state-inline.compact {
    margin-bottom: 1rem;
    padding: 1rem;
}

.position-work-form-block {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.position-work-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 0.75rem;
    align-items: end;
}

.position-work-submit {
    white-space: nowrap;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #046490;
    box-shadow: 0 0 0 3px rgba(4, 100, 144, 0.1);
}

.btn-primary {
    padding: 0.75rem 1.5rem;
    background: #046490;
    color: white;
    border: none;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
    background: #003859;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-delete {
    padding: 0.375rem 0.75rem;
    background: #fee2e2;
    color: #991b1b;
    border: none;
    border-radius: 0;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-delete:hover:not(:disabled) {
    background: #fecaca;
}

.btn-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.loading,
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.error-state {
    text-align: center;
    padding: 3rem;
    color: #dc2626;
}

.error-state p {
    margin-bottom: 1rem;
    font-size: 1.125rem;
}

.comments-section {
    margin-top: 0;
    border-top: none;
}

.rework-banner {
    border: 1px solid #f59e0b;
    background: #fffbeb;
}

.rework-banner .section-title {
    color: #92400e;
}

.rework-banner .section-subtitle {
    color: #a16207;
}

.rework-banner-content {
    padding: 1rem;
    background: rgba(255, 255, 255, 0.65);
    border: 1px solid #fde68a;
    white-space: pre-wrap;
    color: #92400e;
    font-size: 0.9375rem;
    line-height: 1.6;
}

.comments-list {
    margin-bottom: 1.5rem;
}

.comment-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0;
    margin-bottom: 1rem;
}

.comment-content {
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
}

.comment-form {
    margin-top: 1.5rem;
}

.form-textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0;
    font-size: 0.875rem;
    font-family: inherit;
    transition: all 0.2s;
    resize: vertical;
    min-height: 80px;
}

.form-textarea:focus {
    outline: none;
    border-color: #046490;
    box-shadow: 0 0 0 3px rgba(4, 100, 144, 0.1);
}

.btn-save-comment {
    margin-top: 0.75rem;
}

.completion-panel {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
}

.completion-summary {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #003859;
    margin-bottom: 0.5rem;
}

.completion-hint {
    color: #92400e;
    font-size: 0.875rem;
}

.completion-hint.success {
    color: #065f46;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-page-content {
        padding: 0.75rem;
        border-radius: 0;
    }

    .order-content {
        gap: 0.75rem;
    }

    .order-header-section {
        padding: 1rem;
    }

    .order-header-top {
        flex-direction: column;
        align-items: stretch;
    }

    .order-title {
        font-size: 1.25rem;
    }

    .status-actions {
        flex-direction: column;
    }

    .status-actions .btn-status {
        width: 100%;
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .semantic-stack,
    .support-grid {
        gap: 0.75rem;
    }

    .order-info-section,
    .support-card,
    .rework-banner {
        padding: 1rem;
    }

    .section-title {
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }

    .order-info-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .info-card {
        padding: 0.75rem;
    }

    .info-item {
        padding: 0.5rem 0;
    }

    .info-label {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 0.8125rem;
    }

    .form-group {
        margin-bottom: 0.75rem;
    }

    .form-label {
        font-size: 0.8125rem;
        margin-bottom: 0.375rem;
    }

    .form-input,
    .form-textarea {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .works-section {
        padding: 0.75rem;
        border-radius: 0;
    }

    .position-card-header,
    .position-work-form {
        grid-template-columns: 1fr;
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

    .section-title {
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }

    .work-item {
        padding: 0.75rem;
        border-radius: 0;
    }

    .btn-save-comment {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
        margin-top: 0.5rem;
    }
}
</style>
