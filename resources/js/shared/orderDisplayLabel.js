/**
 * Лейбл заказа для UI: number или fallback #id.
 * @param {{ number?: string|null, id?: number|string|null }|null|undefined} order
 * @returns {string}
 */
export function orderDisplayLabel(order) {
    if (order?.number) {
        return String(order.number);
    }
    if (order?.id != null && order.id !== "") {
        return `#${order.id}`;
    }
    return "—";
}
