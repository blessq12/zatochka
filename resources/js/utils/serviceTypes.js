const SERVICE_TYPE_LABELS = {
    sharpening: "Заточка",
    repair: "Ремонт",
};

export function formatServiceTypes(serviceTypes) {
    if (!Array.isArray(serviceTypes) || serviceTypes.length === 0) {
        return "—";
    }

    return serviceTypes
        .map((type) => SERVICE_TYPE_LABELS[type] || type)
        .join(", ");
}
