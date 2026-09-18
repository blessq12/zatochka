const SERVICE_TYPE_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

const ORDER_STATUS_LABELS = {
    created: "Создан",
    master_assigned: "Мастер назначен",
    in_progress: "В работе",
    waiting_parts: "Ожидает запчасти",
    works_completed: "Работы завершены",
    ready: "Готов к выдаче",
    cancelled: "Отменён",
    issued: "Выдан",
};

const BILLING_TYPE_LABELS = {
    paid: "Платный",
    warranty: "Гарантийный",
};

const URGENCY_LABELS = {
    normal: "Обычный",
    urgent: "Срочный",
};

export function formatServiceTypes(serviceTypes) {
    if (Array.isArray(serviceTypes) && serviceTypes.length > 0) {
        return serviceTypes
            .map((type) => SERVICE_TYPE_LABELS[type] || "Услуга")
            .join(", ");
    }

    return "—";
}

export function formatOrderItems(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return "—";
    }

    return items
        .map((item) => {
            if (item.kind === "sharpening") {
                return `${item.title || "Заточка"} × ${item.quantity || 1}`;
            }
            if (item.kind === "repair") {
                const problem = item.problem ? ` — ${item.problem}` : "";
                return `Ремонт #${item.equipment_id || "—"}${problem}`;
            }
            return SERVICE_TYPE_LABELS[item.kind] || item.kind || "Позиция";
        })
        .join(", ");
}

export function serviceTypesFromItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }
    return [...new Set(items.map((item) => item.kind).filter(Boolean))];
}

export function formatOrderStatus(status) {
    if (!status) {
        return "—";
    }

    return ORDER_STATUS_LABELS[status] || "Статус неизвестен";
}

export function formatBillingType(billingType) {
    if (!billingType) {
        return "—";
    }

    return BILLING_TYPE_LABELS[billingType] || "—";
}

export function formatUrgency(urgency) {
    if (!urgency) {
        return "—";
    }

    return URGENCY_LABELS[urgency] || "—";
}
