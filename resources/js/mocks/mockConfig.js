const CLIENT_PORTAL_PREFIXES = [
    "/api/auth/",
    "/api/client/",
    "/api/leads",
    "/api/bootstrap",
];

/** Глобальный переключатель моков. */
export const useApiMocks = () =>
    import.meta.env.VITE_USE_API_MOCKS !== "false";

/** Клиентский API на моках — только явное VITE_USE_CLIENT_API_MOCKS=true. */
export const useClientApiMocks = () =>
    import.meta.env.VITE_USE_CLIENT_API_MOCKS === "true";

/** POS API на моках — только явное VITE_USE_POS_API_MOCKS=true. */
export const usePosApiMocks = () =>
    import.meta.env.VITE_USE_POS_API_MOCKS === "true";

export const isClientPortalRequest = (url) => {
    const path = String(url || "").split("?")[0];

    return CLIENT_PORTAL_PREFIXES.some((prefix) => {
        const normalized = prefix.endsWith("/")
            ? prefix
            : `${prefix}/`;

        if (path === prefix.replace(/\/$/, "") || path === prefix) {
            return true;
        }

        return path.startsWith(normalized) || path.startsWith(prefix);
    });
};

export const isPosRequest = (url) => {
    const path = String(url || "").split("?")[0];

    return path === "/api/pos" || path.startsWith("/api/pos/");
};

export const shouldMockRequest = (config) => {
    if (!useApiMocks()) {
        return false;
    }

    const url = String(config.url || "");
    if (!url.startsWith("/api/")) {
        return false;
    }

    if (!useClientApiMocks() && isClientPortalRequest(url)) {
        return false;
    }

    if (!usePosApiMocks() && isPosRequest(url)) {
        return false;
    }

    return true;
};
