/**
 * Общее отображение заказа в POS (карточка, модалка, экран заказа).
 */

export function formatPosOrderPaymentType(order) {
    if (!order?.order_payment_type) {
        return "—";
    }
    return order.order_payment_type === "paid" ? "Платный" : "Гарантийный";
}

/** Строки компонентов оборудования: { name, serial_number } */
export function getEquipmentSerialRows(equipment) {
    if (!equipment?.serial_number || !Array.isArray(equipment.serial_number)) {
        return [];
    }
    return equipment.serial_number.filter(
        (row) =>
            (row?.name && String(row.name).trim() !== "") ||
            (row?.serial_number && String(row.serial_number).trim() !== "")
    );
}

export function getEquipmentBrandModelLine(equipment) {
    if (!equipment) {
        return "";
    }
    const brand =
        equipment.manufacturer ||
        equipment.brand ||
        equipment.attributes?.brand ||
        "";
    const model = equipment.model || "";
    const parts = [brand, model].filter(Boolean);
    return parts.join(" ").trim();
}
