function buildSharpeningIntake(formData) {
    return {
        tool_type: formData.tool_type || null,
        tools_count: formData.tools_count
            ? Number(formData.tools_count)
            : null,
        extra_comment: formData.comment?.trim() || null,
    };
}

function buildRepairIntake(formData) {
    return {
        equipment_type: formData.equipment_type || null,
        device_name: formData.device_name?.trim() || null,
        problem_description: formData.problem_description?.trim() || null,
        urgency_type: formData.urgency_type || "standard",
    };
}

export default function createPublicOrderRequestDto({
    serviceType = "sharpening",
    formData = {},
} = {}) {
    const intakeData =
        serviceType === "repair"
            ? buildRepairIntake(formData)
            : buildSharpeningIntake(formData);

    return {
        full_name: formData.name || "",
        phone: formData.phone || "",
        service_type: serviceType === "repair" ? "repair" : "sharpening",
        comment:
            serviceType === "sharpening"
                ? formData.comment?.trim() || null
                : formData.comment?.trim() || null,
        intake_data: intakeData,
        needs_delivery: Boolean(formData.needs_delivery),
        delivery_address: formData.needs_delivery
            ? formData.delivery_address || formData.address || null
            : null,
    };
}
