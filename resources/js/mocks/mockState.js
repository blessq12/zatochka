import {
    createDemoMaterials,
    createDemoOrders,
    createDemoWorks,
    demoClient,
    demoEquipment,
    demoMaster,
    demoPriceBlocks,
    demoWarehouseItems,
} from "./fixtures.js";

const clone = (value) => JSON.parse(JSON.stringify(value));

export const createMockState = () => {
    const state = {
        clientToken: "mock-client-token",
        posToken: "mock-pos-token",
        client: clone(demoClient),
        master: clone(demoMaster),
        orders: createDemoOrders(),
        clientOrders: [],
        works: createDemoWorks(),
        materials: createDemoMaterials(),
        equipment: clone(demoEquipment),
        warehouseItems: clone(demoWarehouseItems),
        reviews: {},
        telegramCode: "123456",
        nextWorkId: 10,
        nextOrderId: 200,
    };

    state.clientOrders = state.orders
        .filter((order) => order.client?.id === state.client.id)
        .map((order) => ({
            id: order.id,
            order_number: order.order_number,
            service_type: order.service_type,
            status: order.status,
            urgency: order.urgency,
            price: order.price,
            problem_description: order.problem_description,
            created_at: order.created_at,
            updated_at: order.updated_at,
            review_exists: Boolean(state.reviews[order.id]),
            review: state.reviews[order.id] || null,
        }));

    return state;
};

let state = createMockState();

export const getMockState = () => state;

export const resetMockState = () => {
    state = createMockState();
    return state;
};

export const filterPosOrders = (status) => {
    const orders = getMockState().orders.filter((order) => order.master_id === 1);

    if (status === "new") {
        return orders.filter((order) => order.status === "new");
    }
    if (status === "active") {
        return orders.filter((order) => order.status === "in_work");
    }
    if (status === "waiting_parts") {
        return orders.filter((order) => order.status === "waiting_parts");
    }
    if (status === "completed") {
        return orders.filter((order) =>
            ["ready", "cancelled"].includes(order.status)
        );
    }

    return orders;
};

export const countPosOrders = () => {
    const orders = getMockState().orders.filter((order) => order.master_id === 1);

    return {
        new: orders.filter((order) => order.status === "new").length,
        in_work: orders.filter((order) => order.status === "in_work").length,
        waiting_parts: orders.filter((order) => order.status === "waiting_parts")
            .length,
        ready: orders.filter((order) => order.status === "ready").length,
    };
};

export const findOrder = (orderId) =>
    getMockState().orders.find((order) => String(order.id) === String(orderId));

export const paginate = (items, page = 1, perPage = 10) => {
    const currentPage = Math.max(1, Number(page) || 1);
    const size = Math.max(1, Number(perPage) || 10);
    const total = items.length;
    const lastPage = Math.max(1, Math.ceil(total / size));
    const offset = (currentPage - 1) * size;

    return {
        items: items.slice(offset, offset + size),
        pagination: {
            current_page: currentPage,
            last_page: lastPage,
            per_page: size,
            total,
            has_more_pages: currentPage < lastPage,
        },
    };
};

export const getDashboardStats = () => {
    const counts = countPosOrders();

    return {
        status_stats: counts,
        today: { orders: 2, revenue: 4700 },
        week: { orders: 8, revenue: 21400 },
        month: { orders: 24, revenue: 87300 },
        works: { total: 12, completed: 9 },
    };
};

export const getPriceBlocks = (type) => {
    if (type === "sharpening") {
        return { priceBlocks: clone(demoPriceBlocks.sharpening) };
    }
    if (type === "repair") {
        return { priceBlocks: clone(demoPriceBlocks.repair) };
    }

    return {
        sharpeningBlocks: clone(demoPriceBlocks.sharpening),
        repairBlocks: clone(demoPriceBlocks.repair),
    };
};
