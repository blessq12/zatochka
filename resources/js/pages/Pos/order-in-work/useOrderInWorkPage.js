import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    formatPosOrderPaymentType,
    getEquipmentBrandModelLine,
    getEquipmentSerialRows,
} from "../../../composables/usePosOrderDisplay.js";
import {
    orderListRouteNameForStatus,
    POS_ORDER_TABS,
} from "../../../composables/usePosOrderTabs.js";
import { orderService } from "../../../services/pos/OrderService.js";
import { toastService } from "../../../services/toastService.js";
import { usePosStore } from "../../../stores/posStore.js";

export function useOrderInWorkPage() {
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
        () => order.value?.work_target_mode === "equipment_component"
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

    /** All order items are fully rejected — finish is allowed with zero works. */
    const canFinishWithoutWorks = computed(() => {
        const items = order.value?.items || [];

        return items.length > 0 && repairableItems.value.length === 0;
    });

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
            const orderData = await orderService.getOrderById(orderId.value);
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
        } else if (!confirm("Отметить оборудование как неремонтопригодное?")) {
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
                order.value.order_id,
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

        if (works.value.length === 0 && !canFinishWithoutWorks.value) {
            toastService.error(
                "Нельзя завершить заказ без выполненных работ. Добавьте работы по позициям."
            );
            return;
        }

        if (itemsWithoutWorks.value.length > 0) {
            toastService.error(
                "Укажите выполненные работы по каждой позиции заказа"
            );
            return;
        }

        const confirmMessage = canFinishWithoutWorks.value
            ? "Все позиции неремонтопригодны. Завершить заказ без работ и передать менеджеру?"
            : "Завершить работу по заказу? Заказ уйдёт менеджеру на оценку стоимости.";

        if (!confirm(confirmMessage)) {
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
        return (
            status === "ready" ||
            status === "issued" ||
            status === "cancelled"
        );
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

        if (works.value.length === 0 && !canFinishWithoutWorks.value) {
            return "Нельзя завершить заказ без выполненных работ";
        }

        if (itemsWithoutWorks.value.length > 0) {
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
        canFinishWithoutWorks,
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
}
