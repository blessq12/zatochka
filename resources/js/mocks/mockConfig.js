const CLIENT_PORTAL_PREFIXES = [
    "/api/auth/",
    "/api/client/",
    "/api/leads",
    "/api/bootstrap",
];

/** Глобальный переключатель моков (POS и прочее). */
export const useApiMocks = () =>
    import.meta.env.VITE_USE_API_MOCKS !== "false";

/** Клиентский API на моках — только явное VITE_USE_CLIENT_API_MOCKS=true. */
export const useClientApiMocks = () =>
    import.meta.env.VITE_USE_CLIENT_API_MOCKS === "true";

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

    return true;
};
