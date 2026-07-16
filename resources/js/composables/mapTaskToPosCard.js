/**
 * Маппинг enriched ProductionTask card (API) → формат карточки POS.
 */
export function mapTaskToPosCard(task) {
    if (!task) {
        return null;
    }

    const works = (task.works || []).map((work, index) => ({
        id: work.id,
        description: work.description,
        sort_order: work.sort_order ?? index + 1,
        created_at: work.created_at,
        order_item_id: work.order_item_id ?? work.orderItemId ?? null,
        equipment_component_id:
            work.equipment_component_id ?? work.equipmentComponentId ?? null,
    }));

    const items = (task.items || []).map((item) => ({
        id: item.id,
        tool_name: item.tool_name ?? item.toolName ?? null,
        tool_type: item.tool_type ?? item.toolType ?? null,
        quantity: item.quantity ?? null,
        rejected_quantity: item.rejected_quantity ?? item.rejectedQuantity ?? 0,
        repairable_quantity:
            item.repairable_quantity ?? item.repairableQuantity ?? null,
        status: item.status,
        client_equipment_id:
            item.client_equipment_id ?? item.clientEquipmentId ?? null,
        components: (item.components || []).map((component) => ({
            id: component.id,
            name: component.name,
            serial_number:
                component.serial_number ?? component.serialNumber ?? null,
        })),
    }));

    const toolsSummary = (task.toolsSummary || task.tools_summary || []).map(
        (tool) => ({
            tool_type: tool.tool_type ?? tool.toolType ?? null,
            name: tool.name ?? null,
            quantity: tool.quantity ?? 1,
            rejected_quantity:
                tool.rejected_quantity ?? tool.rejectedQuantity ?? 0,
            repairable_quantity:
                tool.repairable_quantity ?? tool.repairableQuantity ?? null,
        })
    );

    const equipmentList = task.equipmentList || task.equipment_list || [];
    const firstEquipment = equipmentList[0] || null;

    const serviceType = task.serviceType || "";
    const billingType = task.billingType || "";

    return {
        id: task.id,
        order_id: task.orderId,
        order_number: task.orderNumber,
        status: task.posStatus || task.status,
        production_status: task.status,
        urgency: task.urgency,
        needs_delivery: !!task.deliveryRequired,
        delivery_required: !!task.deliveryRequired,
        service_type: serviceType,
        service_type_label:
            serviceType === "repair"
                ? "Ремонт"
                : serviceType === "sharpening"
                  ? "Заточка"
                  : serviceType || "—",
        order_payment_type: billingType,
        is_warranty: billingType === "warranty",
        client_name: task.clientName,
        client_phone: task.clientPhone,
        subject_line: task.subjectLine,
        problem_excerpt: task.problemExcerpt,
        defects: task.defects,
        problem_description: task.defects,
        internal_notes: task.internalNotes,
        manager_rework_comment:
            task.managerReworkComment ?? task.manager_rework_comment ?? null,
        master_internal_comments: (task.masterInternalComments || []).map((comment) => ({
            id: comment.id,
            text: comment.text,
            created_at: comment.created_at ?? comment.createdAt,
        })),
        works,
        works_count: works.length,
        items,
        tools: toolsSummary.map((tool, idx) => ({
            id: idx + 1,
            tool_type: tool.tool_type,
            tool_type_label: tool.tool_type,
            quantity: tool.quantity,
            rejected_quantity: tool.rejected_quantity,
            repairable_quantity: tool.repairable_quantity,
            description: tool.name,
        })),
        tools_summary: toolsSummary,
        equipment: firstEquipment,
        equipment_list: equipmentList,
        equipment_summary: firstEquipment
            ? [firstEquipment.brand, firstEquipment.model]
                  .filter(Boolean)
                  .join(" ")
            : null,
        created_at: task.createdAt,
        master_id: task.masterId,
    };
}
