import axios from "axios";

const formatEquipmentItem = (item) => {
    const name = item.title || item.name || "";
    const brand = item.brand || "";
    const model = item.modelName || item.model || "";
    const brandModel = [brand, model].filter(Boolean).join(" ");
    const fullName = brandModel ? `${name} (${brandModel})` : name;

    const components = item.components || [];
    const serialNumbers = {};
    for (const component of components) {
        if (component.serialNumber || component.serial_number) {
            serialNumbers[component.name] =
                component.serialNumber || component.serial_number;
        }
    }

    let serialNumbersDisplay = "—";
    if (Object.keys(serialNumbers).length > 0) {
        serialNumbersDisplay = Object.entries(serialNumbers)
            .map(([component, serial]) => `${component}: ${serial}`)
            .join(", ");
    }

    return {
        id: item.id,
        name,
        title: name,
        brand,
        model,
        full_name: fullName,
        serial_numbers: serialNumbers,
        serial_numbers_display: serialNumbersDisplay,
        components,
    };
};

export const equipmentService = {
    async search(query) {
        const trimmed = query.trim();
        const response = await axios.get("/api/v1/equipment", {
            params: { query: trimmed, page: 1, per_page: 20 },
        });

        return (response.data.data || []).map(formatEquipmentItem);
    },

    async getOrderHistory(equipmentId) {
        const response = await axios.get(
            `/api/v1/equipment/${equipmentId}/orders`
        );

        return {
            equipment: null,
            orders: response.data.data || [],
        };
    },
};
