import "bootstrap";
import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

axios.interceptors.request.use((config) => {
    if (config.headers.Authorization) {
        return config;
    }

    const url = config.url || "";
    const publicPaths = [
        "/api/auth/login",
        "/api/auth/register",
        "/api/bootstrap",
        "/api/reviews",
        "/api/public/",
    ];
    const isPublic = publicPaths.some((path) => url.startsWith(path));

    if (url.startsWith("/api/") && !url.startsWith("/api/v1/") && !isPublic) {
        const clientToken = localStorage.getItem("auth_token");
        if (clientToken) {
            config.headers.Authorization = `Bearer ${clientToken}`;
        }
    }

    return config;
});
