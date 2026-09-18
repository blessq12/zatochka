import "bootstrap";
import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.headers.common["Accept"] = "application/json";
window.axios.defaults.withCredentials = true;

axios.interceptors.request.use((config) => {
    if (config.headers.Authorization) {
        return config;
    }

    const url = config.url || "";
    const publicPaths = [
        "/api/identity/login",
        "/api/identity/register",
    ];
    const isPublic = publicPaths.some((path) => url.startsWith(path));

    if (url.includes("/api/") && !isPublic) {
        const clientToken = localStorage.getItem("auth_token");
        if (clientToken) {
            config.headers.Authorization = `Bearer ${clientToken}`;
        }
    }

    return config;
});
