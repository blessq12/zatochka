/**
 * Общее отображение заказа в POS (карточка, модалка, экран заказа).
 */

export function formatPosOrderPaymentType(order) {
    if (order?.is_warranty === true) {
        return "Гарантийный";
    }
    if (order?.is_warranty === false) {
        return "Платный";
    }
    if (!order?.order_payment_type) {
        return "—";
    }
    return order.order_payment_type === "paid" ? "Платный" : "Гарантийный";
}

/** Строки компонентов оборудования: { name, serial_number } */
export function getEquipmentSerialRows(equipment) {
    if (!equipment) {
        return [];
    }

    if (Array.isArray(equipment.serial_numbers)) {
        return equipment.serial_numbers
            .filter((sn) => sn && String(sn).trim() !== "")
            .map((sn) => ({ name: null, serial_number: String(sn) }));
    }

    if (!equipment.serial_number || !Array.isArray(equipment.serial_number)) {
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
