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

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

themeTogglerService.init();

// Настройка axios interceptor для обработки 403 ошибок
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 403) {
            router.push({ name: "Forbidden" });
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);
const pinia = createPinia();

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
