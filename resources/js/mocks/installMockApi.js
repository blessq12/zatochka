import { resolveMockResponse } from "./handlers.js";
import { shouldMockRequest, useApiMocks, useClientApiMocks } from "./mockConfig.js";

const createMockAdapter = (config) =>
    Promise.resolve({
        ...resolveMockResponse(config),
        config,
    });

export const installMockApi = (axiosInstance) => {
    if (!useApiMocks()) {
        return;
    }

    axiosInstance.interceptors.request.use((config) => {
        if (!shouldMockRequest(config)) {
            return config;
        }

        config.adapter = () => createMockAdapter(config);
        return config;
    });

    if (import.meta.env.DEV && !useClientApiMocks()) {
        console.info("[mock-api] Клиентский API на бэкенде (моки клиента выключены)");
    }
};
