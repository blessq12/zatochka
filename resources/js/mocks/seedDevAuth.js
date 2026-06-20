import { usePosStore } from "../stores/posStore.js";
import { useAuthStore } from "../stores/authStore.js";
import { demoMaster } from "./fixtures.js";
import { countPosOrders } from "./mockState.js";
import { useClientApiMocks } from "./mockConfig.js";

const CLIENT_TOKEN = "mock-client-token";
const POS_TOKEN = "mock-pos-token";

const clone = (value) => JSON.parse(JSON.stringify(value));

export const isDevAutoAuthEnabled = () =>
    import.meta.env.DEV && import.meta.env.VITE_DEV_AUTO_AUTH === "true";

const clearClientMockSession = (pinia) => {
    const token = localStorage.getItem("auth_token");
    if (token !== CLIENT_TOKEN) {
        return;
    }

    localStorage.removeItem("auth_token");
    useAuthStore(pinia).logout();
};

/**
 * Инициализация dev-окружения.
 * Клиентский контур — только бэкенд: без моков и без автовхода.
 * POS-автовход — через VITE_DEV_AUTO_AUTH=true.
 */
export const initDevEnvironment = (pinia) => {
    if (!useClientApiMocks()) {
        clearClientMockSession(pinia);
    }

    if (!isDevAutoAuthEnabled()) {
        return;
    }

    localStorage.setItem("pos_token", POS_TOKEN);

    usePosStore(pinia).$patch({
        token: POS_TOKEN,
        user: clone(demoMaster),
        ordersCount: countPosOrders(),
    });

    console.info("[dev-auth] Автовход POS (VITE_DEV_AUTO_AUTH=true)");
};

/** @deprecated используй initDevEnvironment */
export const seedDevAuth = initDevEnvironment;
