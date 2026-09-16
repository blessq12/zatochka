import axios from "axios";
import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
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
            url.startsWith("/api/v1/") &&
            !url.startsWith("/api/v1/auth/manager/login");

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
app.use(Toast, {
    position: "top-right",
    timeout: 4000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false,
});

managerStore.restoreSession();
app.mount("#app");
