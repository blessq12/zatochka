/**
 * @param {string | null | undefined} value
 * @returns {string}
 */
export function formatOrderDate(value) {
    if (!value) {
        return "—";
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return "—";
    }

    return date.toLocaleDateString("ru-RU");
}
