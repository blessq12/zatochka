import axios from "axios";
import { createPinia } from "pinia";
import { createApp } from "vue";
import "./bootstrap";
import App from "./App.vue";
import router from "./router";
import { useAuthStore } from "./stores/authStore.js";
import { installPhoneMask } from "@shared/phoneMask.js";
import { registerPwa } from "@shared/registerPwa.js";
import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();
registerPwa({ swUrl: "/pwa/client/sw.js", scope: "/client/" });

const app = createApp(App);
const pinia = createPinia();
const authStore = useAuthStore(pinia);
let isHandlingUnauthorized = false;

const skipUnauthorizedLogout = (url) =>
    url.includes("/api/identity/login") ||
    url.includes("/api/identity/register") ||
    url.includes("/api/identity/me") ||
    url.includes("/api/identity/logout");

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        const url = error.config?.url || "";
        const isClientApi = url.includes("/api/") && !skipUnauthorizedLogout(url);

        if (
            status === 401 &&
            isClientApi &&
            !isHandlingUnauthorized &&
            !authStore.isRestoring
        ) {
            isHandlingUnauthorized = true;
            await authStore.clearSession();
            try {
                if (router.currentRoute.value.name !== "client.login") {
                    await router.push({ name: "client.login" });
                }
            } finally {
                isHandlingUnauthorized = false;
            }
        }

        return Promise.reject(error);
    },
);

installPhoneMask(app);
app.use(pinia);
app.use(router);

authStore.restoreSession().finally(() => {
    app.mount("#app");
});
