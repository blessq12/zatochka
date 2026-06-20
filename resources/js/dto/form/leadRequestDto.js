const TOOL_TYPE_LABELS = {
    manicure: "Маникюрные",
    hair: "Парикмахерские",
    grooming: "Грумерские",
    barber: "Барберские",
    other: "Другие",
};

function buildSharpeningComment(formData) {
    const parts = [];

    if (formData.tool_type) {
        parts.push(
            `Тип инструментов: ${TOOL_TYPE_LABELS[formData.tool_type] || formData.tool_type}`
        );
    }

    if (formData.tools_count) {
        parts.push(`Количество: ${formData.tools_count}`);
    }

    if (formData.comment?.trim()) {
        parts.push(formData.comment.trim());
    }

    return parts.join(". ") || null;
}

function buildRepairComment(formData) {
    const parts = [];

    if (formData.equipment_type) {
        parts.push(`Тип оборудования: ${formData.equipment_type}`);
    }

    if (formData.device_name || formData.equipment_name) {
        parts.push(
            `Наименование: ${formData.device_name || formData.equipment_name}`
        );
    }

    if (formData.problem_description?.trim()) {
        parts.push(formData.problem_description.trim());
    }

    if (formData.urgency_type === "urgent") {
        parts.push("Срочность: срочный");
    }

    return parts.join(". ") || null;
}

export default function createLeadRequestDto({
    serviceType = "sharpening",
    formData = {},
} = {}) {
    const comment =
        serviceType === "repair"
            ? buildRepairComment(formData)
            : buildSharpeningComment(formData);

    return {
        full_name: formData.name || "",
        phone: formData.phone || "",
        email: formData.email || null,
        service_types: [serviceType === "repair" ? "repair" : "sharpening"],
        comment,
        needs_delivery: Boolean(formData.needs_delivery),
        delivery_address: formData.needs_delivery
            ? formData.delivery_address || formData.address || null
            : null,
    };
}
