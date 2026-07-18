const SERVICE_TYPE_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

const ORDER_STATUS_LABELS = {
    created: "Создан",
    master_assigned: "Мастер назначен",
    reception_completed: "Приёмка завершена",
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

const REVIEW_STATUS_LABELS = {
    pending: "На модерации",
    pending_moderation: "На модерации",
    published: "Опубликован",
    rejected: "Отклонён",
    hidden: "Скрыт",
    deleted: "Удалён",
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

export function formatReviewStatus(status) {
    if (!status) {
        return "—";
    }

    return REVIEW_STATUS_LABELS[status] || "Статус отзыва неизвестен";
}

export function formatStars(rating) {
    const value = Math.max(0, Math.min(5, Number(rating) || 0));
    return "★".repeat(value) + "☆".repeat(5 - value);
}
