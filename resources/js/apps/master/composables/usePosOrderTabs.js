/**
 * Вкладки воронки заказов POS: маршруты, лейблы, конфиг списка.
 */

export const POS_ORDER_TAB_KEYS = [
    "new",
    "in_work",
    "waiting_parts",
    "ready",
];

/** @type {Record<string, { routeName: string, apiStatus: string|null, label: string, emptyMessage: string, countKey: string, primaryAction: object|null }>} */
export const POS_ORDER_TABS = {
    new: {
        routeName: "pos.orders.new",
        apiStatus: "new",
        label: "Новые",
        emptyMessage: "Новых заказов нет",
        countKey: "new",
        primaryAction: {
            text: "Взять в работу",
            loadingText: "Сохранение...",
            className: "btn-status-in-work",
            type: "takeToWork",
        },
    },
    in_work: {
        routeName: "pos.orders.in-work",
        apiStatus: "active",
        label: "В работе",
        emptyMessage: "Заказов в работе нет",
        countKey: "in_work",
        primaryAction: {
            text: "Открыть заказ",
            className: "btn-work",
            type: "openDetail",
        },
    },
    waiting_parts: {
        routeName: "pos.orders.waiting-parts",
        apiStatus: "waiting_parts",
        label: "Ожидание запчастей",
        emptyMessage: "Заказов в ожидании запчастей нет",
        countKey: "waiting_parts",
        primaryAction: {
            text: "Открыть заказ",
            className: "btn-work",
            type: "openDetail",
        },
    },
    ready: {
        routeName: "pos.orders.ready",
        apiStatus: "completed",
        label: "Выполнено",
        emptyMessage: "Выполненных заказов нет",
        countKey: "ready",
        primaryAction: {
            text: "Просмотр",
            className: "btn-work",
            type: "openModal",
        },
    },
};

/** @returns {typeof POS_ORDER_TABS[keyof typeof POS_ORDER_TABS][]} */
export function posOrderTabList() {
    return POS_ORDER_TAB_KEYS.map((key) => POS_ORDER_TABS[key]);
}

/** @param {import('vue-router').RouteLocationNormalizedLoaded} route */
export function resolvePosOrderTabKey(route) {
    const tab = route.meta?.posOrderTab;
    if (typeof tab === "string" && POS_ORDER_TABS[tab]) {
        return tab;
    }

    const match = posOrderTabList().find(
        (item) => item.routeName === route.name
    );

    return match ? POS_ORDER_TAB_KEYS.find((k) => POS_ORDER_TABS[k] === match) : null;
}

/** @param {string|null|undefined} status */
export function orderListRouteNameForStatus(status) {
    switch (status) {
        case "new":
            return POS_ORDER_TABS.new.routeName;
        case "in_work":
            return POS_ORDER_TABS.in_work.routeName;
        case "waiting_parts":
            return POS_ORDER_TABS.waiting_parts.routeName;
        case "ready":
            return POS_ORDER_TABS.ready.routeName;
        default:
            return POS_ORDER_TABS.in_work.routeName;
    }
}

/**
 * @param {string} tabKey
 * @param {Record<string, number>} counts
 */
export function formatPosOrderTabLabel(tabKey, counts) {
    const tab = POS_ORDER_TABS[tabKey];
    if (!tab) {
        return "";
    }

    const count = counts[tab.countKey] ?? 0;

    return `${tab.label} (${count})`;
}
