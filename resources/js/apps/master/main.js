import axios from "axios";
import { createPinia } from "pinia";
import { createApp } from "vue";
import "./bootstrap";
import App from "./App.vue";
import router from "./router";
import { usePosStore } from "./stores/posStore.js";
import { installPhoneMask } from "@shared/phoneMask.js";
import { registerPwa } from "@shared/registerPwa.js";
import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();
registerPwa({ swUrl: "/pwa/master/sw.js", scope: "/master/" });

const app = createApp(App);
const pinia = createPinia();
const posStore = usePosStore(pinia);
let isHandlingPosUnauthorized = false;

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        const url = error.config?.url || "";
        const isPosApiRequest =
            url.startsWith("/api/") &&
            !url.startsWith("/api/identity/login") &&
            !url.startsWith("/api/identity/register");

        if (status === 401 && isPosApiRequest && !isHandlingPosUnauthorized) {
            isHandlingPosUnauthorized = true;
            posStore.logout();

            try {
                await router.push({ name: "pos" });
            } finally {
                isHandlingPosUnauthorized = false;
            }
        }

        return Promise.reject(error);
    }
);

installPhoneMask(app);
app.use(pinia);
app.use(router);

app.mount("#app");
