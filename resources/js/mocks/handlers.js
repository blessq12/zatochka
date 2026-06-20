import {
    countPosOrders,
    filterPosOrders,
    findOrder,
    getBootstrapData,
    getClientOrdersForBucket,
    getDashboardStats,
    getMockState,
    paginate,
} from "./mockState.js";

const json = (data, status = 200) => ({
    data,
    status,
    statusText: status === 200 ? "OK" : "Error",
    headers: {},
    config: {},
});

const parseBody = (config) => {
    if (!config.data) {
        return {};
    }

    if (typeof config.data === "string") {
        try {
            return JSON.parse(config.data);
        } catch {
            return {};
        }
    }

    return config.data;
};

const matchRoute = (method, url) => {
    const normalized = url.split("?")[0];
    const routes = [
        ["GET", /^\/api\/bootstrap$/, "bootstrap"],
        ["POST", /^\/api\/auth\/login$/, "clientLogin"],
        ["POST", /^\/api\/auth\/register$/, "clientRegister"],
        ["GET", /^\/api\/client\/profile$/, "clientProfile"],
        ["PATCH", /^\/api\/client\/profile$/, "clientUpdate"],
        ["POST", /^\/api\/client\/password$/, "clientSetPassword"],
        ["POST", /^\/api\/leads$/, "submitLead"],
        ["GET", /^\/api\/client\/orders\/active$/, "clientActiveOrders"],
        ["GET", /^\/api\/client\/orders\/history$/, "clientHistoryOrders"],
        ["POST", /^\/api\/client\/orders\/(\d+)\/review$/, "createReview"],
        ["POST", /^\/api\/pos\/login$/, "posLogin"],
        ["POST", /^\/api\/pos\/logout$/, "posLogout"],
        ["GET", /^\/api\/pos\/me$/, "posMe"],
        ["POST", /^\/api\/pos\/profile\/update$/, "posProfileUpdate"],
        ["GET", /^\/api\/pos\/dashboard$/, "posDashboard"],
        ["GET", /^\/api\/pos\/orders\/count$/, "posOrdersCount"],
        ["GET", /^\/api\/pos\/orders$/, "posOrdersList"],
        ["GET", /^\/api\/pos\/orders\/(\d+)$/, "posOrderShow"],
        ["PATCH", /^\/api\/pos\/orders\/(\d+)\/update$/, "posOrderUpdate"],
        ["PATCH", /^\/api\/pos\/orders\/(\d+)\/status$/, "posOrderStatus"],
        ["GET", /^\/api\/pos\/orders\/(\d+)\/works$/, "posOrderWorksList"],
        ["POST", /^\/api\/pos\/orders\/(\d+)\/works$/, "posOrderWorkCreate"],
        ["DELETE", /^\/api\/pos\/orders\/(\d+)\/works\/(\d+)$/, "posOrderWorkDelete"],
        ["GET", /^\/api\/pos\/orders\/(\d+)\/materials$/, "posOrderMaterialsList"],
        ["POST", /^\/api\/pos\/orders\/(\d+)\/materials$/, "posOrderMaterialCreate"],
        ["DELETE", /^\/api\/pos\/orders\/(\d+)\/materials\/(\d+)$/, "posOrderMaterialDelete"],
        ["GET", /^\/api\/pos\/equipment\/search$/, "posEquipmentSearch"],
        ["GET", /^\/api\/pos\/equipment\/(\d+)\/orders$/, "posEquipmentOrders"],
        ["GET", /^\/api\/pos\/warehouse\/items$/, "posWarehouseItems"],
        ["POST", /^\/api\/pos\/telegram\/send-verification-code$/, "posTelegramSend"],
        ["POST", /^\/api\/pos\/telegram\/verify-code$/, "posTelegramVerify"],
    ];

    for (const [routeMethod, pattern, name] of routes) {
        if (routeMethod !== method) {
            continue;
        }

        const match = normalized.match(pattern);
        if (match) {
            return { name, params: match.slice(1) };
        }
    }

    return null;
};

const handlers = {
    bootstrap() {
        return json({ data: getBootstrapData() });
    },

    clientLogin(_config, body) {
        const state = getMockState();
        state.client = {
            ...state.client,
            phone: body.phone || state.client.phone,
        };

        return json({
            token: state.clientToken,
            client: {
                ...state.client,
                requires_password_set: false,
            },
        });
    },

    clientRegister(_config, body) {
        const state = getMockState();
        state.client = {
            ...state.client,
            id: 1,
            full_name: body.full_name || "Новый клиент",
            phone: body.phone || state.client.phone,
            email: body.email || state.client.email,
        };

        return json(
            {
                token: state.clientToken,
                client: state.client,
            },
            201
        );
    },

    clientProfile() {
        return json({ data: getMockState().client });
    },

    clientUpdate(_config, body) {
        const state = getMockState();
        state.client = {
            ...state.client,
            ...body,
            id: state.client.id,
            phone: state.client.phone,
        };

        return json({ data: state.client });
    },

    clientSetPassword() {
        const state = getMockState();
        state.client = {
            ...state.client,
            requires_password_set: false,
        };

        return json({ data: state.client });
    },

    submitLead() {
        return json(
            {
                data: {
                    id: 1,
                    message: "Заявка принята. Менеджер свяжется с вами.",
                },
            },
            201
        );
    },

    clientActiveOrders(config) {
        const params = config.params || {};
        const result = getClientOrdersForBucket(
            "active",
            params.page,
            params.per_page
        );

        return json({
            data: result.items,
            meta: {
                total: result.pagination.total,
                page: result.pagination.current_page,
                per_page: result.pagination.per_page,
            },
        });
    },

    clientHistoryOrders(config) {
        const params = config.params || {};
        const result = getClientOrdersForBucket(
            "history",
            params.page,
            params.per_page
        );

        return json({
            data: result.items,
            meta: {
                total: result.pagination.total,
                page: result.pagination.current_page,
                per_page: result.pagination.per_page,
            },
        });
    },

    createReview(_config, body, params) {
        const state = getMockState();
        const orderId = Number(params[0]);
        const review = {
            id: Object.keys(state.reviews).length + 1,
            order_id: orderId,
            rating: body.rating,
            comment: body.comment,
            status: "pending",
        };

        state.reviews[orderId] = review;

        return json({ data: review }, 201);
    },

    posLogin(_config, body) {
        const state = getMockState();
        state.master = {
            ...state.master,
            email: body.email || state.master.email,
        };

        return json({
            message: "Login successful",
            token: state.posToken,
            user: state.master,
        });
    },

    posLogout() {
        return json({ message: "Logout successful" });
    },

    posMe() {
        return json({ user: getMockState().master });
    },

    posProfileUpdate(_config, body) {
        const state = getMockState();
        state.master = { ...state.master, ...body };
        return json({
            message: "Profile updated successfully",
            user: state.master,
        });
    },

    posDashboard() {
        return json(getDashboardStats());
    },

    posOrdersCount() {
        return json(countPosOrders());
    },

    posOrdersList(config) {
        const status = config.params?.status || null;
        return json({ orders: filterPosOrders(status) });
    },

    posOrderShow(_config, _body, params) {
        const order = findOrder(params[0]);
        if (!order) {
            return json({ message: "Order not found" }, 404);
        }

        return json({ order });
    },

    posOrderUpdate(_config, body, params) {
        const order = findOrder(params[0]);
        if (!order) {
            return json({ message: "Order not found" }, 404);
        }

        Object.assign(order, body, {
            updated_at: new Date().toISOString(),
        });

        return json({
            message: "Order updated",
            order,
        });
    },

    posOrderStatus(_config, body, params) {
        const order = findOrder(params[0]);
        if (!order) {
            return json({ message: "Order not found" }, 404);
        }

        order.status = body.status;
        order.updated_at = new Date().toISOString();

        return json({
            message: "Order status updated",
            order,
        });
    },

    posOrderWorksList(_config, _body, params) {
        const works = getMockState().works[Number(params[0])] || [];
        return json({ works });
    },

    posOrderWorkCreate(_config, body, params) {
        const state = getMockState();
        const orderId = Number(params[0]);
        const work = {
            id: state.nextWorkId++,
            order_id: orderId,
            description: body.description,
            equipment_component_name: body.equipment_component_name || null,
            equipment_component_serial_number:
                body.equipment_component_serial_number || null,
            created_at: new Date().toISOString(),
        };

        if (!state.works[orderId]) {
            state.works[orderId] = [];
        }
        state.works[orderId].push(work);

        return json({ message: "Work created successfully", work }, 201);
    },

    posOrderWorkDelete(_config, _body, params) {
        const state = getMockState();
        const orderId = Number(params[0]);
        const workId = Number(params[1]);
        state.works[orderId] = (state.works[orderId] || []).filter(
            (work) => work.id !== workId
        );

        return json({ message: "Work deleted successfully" });
    },

    posOrderMaterialsList(_config, _body, params) {
        const materials = getMockState().materials[Number(params[0])] || [];
        return json({ materials });
    },

    posOrderMaterialCreate() {
        return json(
            {
                message:
                    "Добавление запчастей и материалов через POS отключено. Используйте панель менеджера.",
            },
            403
        );
    },

    posOrderMaterialDelete() {
        return json(
            {
                message:
                    "Удаление запчастей и материалов через POS отключено. Используйте панель менеджера.",
            },
            403
        );
    },

    posEquipmentSearch(config) {
        const q = String(config.params?.q || "").trim().toLowerCase();
        const equipment = getMockState().equipment.filter((item) => {
            if (!q || q.length < 2) {
                return false;
            }

            return (
                item.name.toLowerCase().includes(q) ||
                item.full_name.toLowerCase().includes(q)
            );
        });

        return json({ equipment });
    },

    posEquipmentOrders(_config, _body, params) {
        const equipmentId = Number(params[0]);
        const equipment = getMockState().equipment.find(
            (item) => item.id === equipmentId
        );
        const orders = getMockState().orders.filter(
            (order) => order.equipment?.id === equipmentId
        );

        return json({ equipment, orders });
    },

    posWarehouseItems(config) {
        const params = config.params || {};
        const result = paginate(
            getMockState().warehouseItems,
            params.page,
            params.per_page
        );

        return json({
            items: result.items,
            pagination: result.pagination,
        });
    },

    posTelegramSend() {
        const state = getMockState();
        return json({
            success: true,
            message: "Verification code sent",
            telegram_username: state.master.telegram_username,
            expires_in_minutes: 5,
        });
    },

    posTelegramVerify(_config, body) {
        const state = getMockState();
        if (body.code !== state.telegramCode) {
            return json(
                { success: false, message: "Invalid or expired verification code" },
                400
            );
        }

        state.master.telegram_verified_at = new Date().toISOString();
        return json({
            success: true,
            message: "Telegram verified",
            telegram_username: state.master.telegram_username,
            verified_at: state.master.telegram_verified_at,
            user: state.master,
        });
    },
};

export const resolveMockResponse = (config) => {
    const method = (config.method || "get").toUpperCase();
    const url = config.url || "";
    const route = matchRoute(method, url);

    if (!route) {
        console.warn(`[mock-api] Нет мока для ${method} ${url}`);
        return json({ message: `Mock not found for ${method} ${url}` }, 404);
    }

    const handler = handlers[route.name];
    const body = parseBody(config);

    return handler(config, body, route.params);
};
