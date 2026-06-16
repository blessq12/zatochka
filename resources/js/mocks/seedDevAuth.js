import { useAuthStore } from "../stores/authStore.js";
import { usePosStore } from "../stores/posStore.js";
import { demoClient, demoMaster } from "./fixtures.js";
import { countPosOrders } from "./mockState.js";

const CLIENT_TOKEN = "mock-client-token";
const POS_TOKEN = "mock-pos-token";

const clone = (value) => JSON.parse(JSON.stringify(value));

export const isDevAutoAuthEnabled = () =>
    import.meta.env.DEV && import.meta.env.VITE_DEV_AUTO_AUTH === "true";

/**
 * Временный автовход для разработки: токены в localStorage + объекты в Pinia.
 * Включается через VITE_DEV_AUTO_AUTH=true (только import.meta.env.DEV).
 */
export const seedDevAuth = (pinia) => {
    if (!isDevAutoAuthEnabled()) {
        return;
    }

    localStorage.setItem("auth_token", CLIENT_TOKEN);
    localStorage.setItem("pos_token", POS_TOKEN);

    const client = clone(demoClient);
    const master = clone(demoMaster);

    useAuthStore(pinia).$patch({
        token: CLIENT_TOKEN,
        user: client,
        requiresPasswordSet: client.requires_password_set === true,
        telegramVerified:
            client.telegram_verified_at !== null &&
            client.telegram_verified_at !== undefined,
    });

    usePosStore(pinia).$patch({
        token: POS_TOKEN,
        user: master,
        ordersCount: countPosOrders(),
    });

    console.info(
        "[dev-auth] Автовход: клиент (ЛК) и мастер (POS). VITE_DEV_AUTO_AUTH=true"
    );
};
