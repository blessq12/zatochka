import axios from "axios";

export const STATUS_LABELS = {
    created: "Создан",
    master_assigned: "Мастер назначен",
    in_progress: "В работе",
    waiting_parts: "Ожидает запчасти",
    approval: "Согласование",
    works_completed: "Работы выполнены",
    ready: "Готов",
    issued: "Выдан",
    cancelled: "Отменён",
};

/** Tailwind-классы заливки кружка статуса в листинге. */
export const STATUS_COLORS = {
    created: "bg-slate-400",
    master_assigned: "bg-sky-500",
    in_progress: "bg-amber-500",
    waiting_parts: "bg-orange-600",
    approval: "bg-rose-500",
    works_completed: "bg-teal-500",
    ready: "bg-emerald-500",
    issued: "bg-green-700",
    cancelled: "bg-red-500",
};

export const STATUS_ORDER = Object.keys(STATUS_LABELS);

export const BILLING_LABELS = {
    paid: "Платный",
    warranty: "Гарантийный",
};

export const URGENCY_LABELS = {
    normal: "Обычный",
    urgent: "Срочный",
};

export const KIND_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

export function statusLabel(status) {
    return STATUS_LABELS[status] || status || "—";
}

export function statusColorClass(status) {
    return STATUS_COLORS[status] || "bg-slate-300";
}

/**
 * Тип заказа по составу позиций: заточка / ремонт / заточка + ремонт.
 * @param {{ items?: Array<{ kind?: string }> } | null | undefined} order
 * @returns {string}
 */
export function compositionLabel(order) {
    const kinds = new Set(
        (order?.items || [])
            .map((item) => item?.kind)
            .filter((kind) => kind === "sharpening" || kind === "repair"),
    );
    const hasSharpening = kinds.has("sharpening");
    const hasRepair = kinds.has("repair");

    if (hasSharpening && hasRepair) {
        return "Заточка + ремонт";
    }
    if (hasRepair) {
        return "Ремонт";
    }
    if (hasSharpening) {
        return "Заточка";
    }

    return "—";
}

export function allowedTransitions(status) {
    switch (status) {
        case "created":
            return ["cancelled"];
        case "master_assigned":
            return ["cancelled"];
        case "in_progress":
            return ["waiting_parts"];
        case "waiting_parts":
            return ["in_progress"];
        case "approval":
            return ["in_progress", "issued"];
        case "works_completed":
            return ["ready", "in_progress"];
        case "ready":
            return ["issued"];
        default:
            return [];
    }
}

/**
 * @typedef {"forward" | "neutral" | "warning" | "danger"} TransitionTone
 * @typedef {{
 *   to: string,
 *   title: string,
 *   hint: string,
 *   tone: TransitionTone,
 *   confirm: string | null,
 * }} TransitionAction
 */

/**
 * Явные менеджерские действия поверх allowedTransitions.
 * @param {string | null | undefined} fromStatus
 * @returns {TransitionAction[]}
 */
export function transitionActions(fromStatus) {
    const from = fromStatus || "";
    return allowedTransitions(from).map((to) => {
        const meta = transitionActionMeta(from, to);
        return {
            to,
            title: meta.title,
            hint: `${statusLabel(from)} → ${statusLabel(to)}`,
            tone: meta.tone,
            confirm: meta.confirm,
        };
    });
}

/**
 * @param {string} from
 * @param {string} to
 * @returns {{ title: string, tone: TransitionTone, confirm: string | null }}
 */
function transitionActionMeta(from, to) {
    const key = `${from}>${to}`;
    switch (key) {
        case "created>cancelled":
        case "master_assigned>cancelled":
            return {
                title: "Отменить заказ",
                tone: "danger",
                confirm: "Отменить заказ? Это действие нельзя откатить.",
            };
        case "in_progress>waiting_parts":
            return {
                title: "Ждать запчасти",
                tone: "neutral",
                confirm: null,
            };
        case "waiting_parts>in_progress":
            return {
                title: "Вернуть в работу",
                tone: "forward",
                confirm: null,
            };
        case "approval>in_progress":
            return {
                title: "Вернуть в работу",
                tone: "forward",
                confirm: null,
            };
        case "approval>issued":
            return {
                title: "Выдать без работ",
                tone: "warning",
                confirm:
                    "Выдать заказ без выполнения работ? Задание мастера будет закрыто.",
            };
        case "works_completed>ready":
            return {
                title: "Отметить готовым",
                tone: "forward",
                confirm: null,
            };
        case "works_completed>in_progress":
            return {
                title: "Вернуть на доработку",
                tone: "warning",
                confirm:
                    "Вернуть заказ мастеру на доработку? Задание снова станет открытым.",
            };
        case "ready>issued":
            return {
                title: "Выдать клиенту",
                tone: "forward",
                confirm: null,
            };
        default:
            return {
                title: `Перевести в «${statusLabel(to)}»`,
                tone: "neutral",
                confirm: null,
            };
    }
}

export const orderService = {
    async list({ clientId = null, status = null } = {}) {
        const params = {};
        if (clientId != null && clientId !== "") {
            params.client_id = clientId;
        }
        if (status) {
            params.status = status;
        }
        const { data } = await axios.get("/api/orders", { params });
        return data.data || [];
    },

    async get(id) {
        const { data } = await axios.get(`/api/orders/${id}`);
        return data;
    },

    async create(payload) {
        const { data } = await axios.post("/api/orders", payload);
        return data;
    },

    async updateItems(id, items) {
        const { data } = await axios.put(`/api/orders/${id}/items`, { items });
        return data;
    },

    async assignMaster(id, masterId) {
        const { data } = await axios.post(`/api/orders/${id}/assign-master`, {
            master_id: masterId,
        });
        return data;
    },

    async transition(id, status) {
        const { data } = await axios.post(`/api/orders/${id}/transition`, {
            status,
        });
        return data;
    },

    async addComment(id, body) {
        const { data } = await axios.post(`/api/orders/${id}/comments`, {
            body,
        });
        return data;
    },

    async resolveApproval(id, status, body) {
        const { data } = await axios.post(
            `/api/orders/${id}/resolve-approval`,
            { status, body },
        );
        return data;
    },
};
