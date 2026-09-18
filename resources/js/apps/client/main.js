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

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        const url = error.config?.url || "";
        const isClientApi =
            url.startsWith("/api/") &&
            !url.startsWith("/api/identity/login") &&
            !url.startsWith("/api/identity/register");

        if (status === 401 && isClientApi && !isHandlingUnauthorized) {
            isHandlingUnauthorized = true;
            await authStore.logout();
            try {
                await router.push({ name: "client.dashboard" });
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

authStore.checkAuth().finally(() => {
    app.mount("#app");
});
