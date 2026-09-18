const SERVICE_TYPE_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

const ORDER_STATUS_LABELS = {
    created: "Создан",
    master_assigned: "Мастер назначен",
    in_progress: "В работе",
    works_completed: "Работы завершены",
    ready: "Готов к выдаче",
    cancelled: "Отменён",
    closed: "Закрыт",
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
    if (!Array.isArray(serviceTypes) || serviceTypes.length === 0) {
        return "—";
    }

    return serviceTypes
        .map((type) => SERVICE_TYPE_LABELS[type] || "Услуга")
        .join(", ");
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
