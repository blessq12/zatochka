/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import axios from "axios";
import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import "./bootstrap";

//services
import App from "./App.vue";
import router from "./router";
import themeTogglerService from "./services/themeTogglerService";
import { usePosStore } from "./stores/posStore.js";

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

themeTogglerService.init();

const app = createApp(App);
const pinia = createPinia();
const posStore = usePosStore(pinia);
let isHandlingPosUnauthorized = false;

// Глобальная обработка ошибок API для клиентской и POS частей.
axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        const url = error.config?.url || "";
        const isPosApiRequest =
            url.startsWith("/api/v1/") && !url.startsWith("/api/v1/auth/login");

        if (status === 401 && isPosApiRequest && !isHandlingPosUnauthorized) {
            isHandlingPosUnauthorized = true;
            posStore.logout();

            try {
                await router.push({ name: "pos" });
            } finally {
                isHandlingPosUnauthorized = false;
            }
        }

        if (status === 403) {
            router.push({ name: "Forbidden" });
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

/**
 * Страницы грузятся dynamic import'ом в router.
 * Остальные компоненты — локальные import у родителя.
 */

app.mount("#app");
