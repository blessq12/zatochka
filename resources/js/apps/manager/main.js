import axios from "axios";
import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import "./bootstrap";
import App from "./App.vue";
import router from "./router";
import { useManagerStore } from "./stores/managerStore.js";
import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();

const app = createApp(App);
const pinia = createPinia();
const managerStore = useManagerStore(pinia);
let isHandlingUnauthorized = false;

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        const url = error.config?.url || "";
        const isManagerApi =
            url.startsWith("/api/") &&
            !url.startsWith("/api/identity/login") &&
            !url.startsWith("/api/identity/register");

        if (status === 401 && isManagerApi && !isHandlingUnauthorized) {
            isHandlingUnauthorized = true;
            await managerStore.logout();
            try {
                await router.push({ name: "manager.login" });
            } finally {
                isHandlingUnauthorized = false;
            }
        }

        return Promise.reject(error);
    }
);

app.directive("maska", vMaska);
app.use(pinia);
app.use(router);

managerStore.restoreSession().finally(() => {
    app.mount("#app");
});
