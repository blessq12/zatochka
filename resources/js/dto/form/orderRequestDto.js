// DTO: Complete order request payload for all form types
// Usage: import createOrderRequestDto from "./orderRequestDto";
//        const payload = createOrderRequestDto({ serviceType, formData });

export default function createOrderRequestDto({
    serviceType = "sharpening",
    formData = {},
} = {}) {
    // Базовые поля для всех типов заказов
    const basePayload = {
        service_type: serviceType,
        client_name: formData.name || "",
        client_phone: formData.phone || "",
        agreement: formData.agreement || false,
        privacy_agreement: formData.privacy_agreement || true,
    };

    // Добавляем специфичные поля в зависимости от типа услуги
    switch (serviceType) {
        case "sharpening":
            return {
                ...basePayload,
                tool_type: formData.tool_type || "",
                total_tools_count: formData.tools_count || "",
                problem_description: formData.comment || "",
                needs_delivery: formData.needs_delivery || false,
                delivery_address: formData.delivery_address || "",
                email: formData.email || null,
            };

        case "repair":
            // Маппинг urgency_type -> urgency
            let urgency = "normal";
            if (formData.urgency_type === "urgent") {
                urgency = "urgent";
            }
            
            return {
                ...basePayload,
                equipment_type: formData.equipment_type || "",
                equipment_name: formData.device_name || "",
                problem_description: formData.problem_description || "",
                urgency: urgency,
                needs_delivery: formData.needs_delivery || false,
                delivery_address: formData.delivery_address || "",
                email: formData.email || null,
            };

        case "delivery":
            return {
                ...basePayload,
                tool_type: formData.tool_type || "",
                total_tools_count: formData.tools_count || "",
                delivery_address: formData.address || "",
                problem_description: formData.comment || "",
            };

        default:
            return basePayload;
    }
}

// Дополнительные функции для создания специфичных DTO
export function createSharpeningOrderDto(formData = {}) {
    return createOrderRequestDto({
        serviceType: "sharpening",
        formData,
    });
}

export function createRepairOrderDto(formData = {}) {
    return createOrderRequestDto({
        serviceType: "repair",
        formData,
    });
}

export function createDeliveryOrderDto(formData = {}) {
    return createOrderRequestDto({
        serviceType: "delivery",
        formData,
    });
}

// Функции для обратной совместимости со старыми DTO
export function createSharpeningOrderRequestDto({
    toolsCount = "",
    toolType = "",
    name = "",
    phone = "",
    comment = "",
    agreement = false,
    privacyAgreement = true,
} = {}) {
    return createOrderRequestDto({
        serviceType: "sharpening",
        formData: {
            tools_count: toolsCount,
            tool_type: toolType,
            name,
            phone,
            comment,
            agreement,
            privacy_agreement: privacyAgreement,
        },
    });
}

export function createRepairOrderRequestDto({
    equipmentName = "",
    equipmentType = "",
    problemDescription = "",
    name = "",
    phone = "",
    urgency = "normal",
    agreement = false,
    privacyAgreement = true,
} = {}) {
    return createOrderRequestDto({
        serviceType: "repair",
        formData: {
            equipment_name: equipmentName,
            equipment_type: equipmentType,
            problem_description: problemDescription,
            name,
            phone,
            urgency,
            agreement,
            privacy_agreement: privacyAgreement,
        },
    });
}

export function createDeliveryOrderRequestDto({
    toolType = "",
    toolsCount = "",
    name = "",
    phone = "",
    address = "",
    comment = "",
    agreement = false,
    privacyAgreement = true,
} = {}) {
    return createOrderRequestDto({
        serviceType: "delivery",
        formData: {
            tool_type: toolType,
            tools_count: toolsCount,
            name,
            phone,
            address,
            comment,
            agreement,
            privacy_agreement: privacyAgreement,
        },
    });
}

export function createUniversalOrderRequestDto({
    serviceType = "sharpening",
    formData = {},
} = {}) {
    return createOrderRequestDto({
        serviceType,
        formData,
    });
}
