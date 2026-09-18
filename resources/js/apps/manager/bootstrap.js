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

    if (
        url.startsWith("/api/identity/login") ||
        url.startsWith("/api/identity/register")
    ) {
        return config;
    }

    if (url.startsWith("/api/")) {
        const token = localStorage.getItem("manager_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
    }

    return config;
});
