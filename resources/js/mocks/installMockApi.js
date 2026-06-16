import { resolveMockResponse } from "./handlers.js";

const useMocks = () => import.meta.env.VITE_USE_API_MOCKS !== "false";

const createMockAdapter = (config) =>
    Promise.resolve({
        ...resolveMockResponse(config),
        config,
    });

export const installMockApi = (axiosInstance) => {
    if (!useMocks()) {
        return;
    }

    axiosInstance.interceptors.request.use((config) => {
        if (!String(config.url || "").startsWith("/api/")) {
            return config;
        }

        config.adapter = () => createMockAdapter(config);
        return config;
    });

    if (import.meta.env.DEV) {
        console.info("[mock-api] Фронт работает на моках API (VITE_USE_API_MOCKS)");
    }
};
