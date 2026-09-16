import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import "./bootstrap.js";
import App from "./App.vue";
import SiteLink from "./components/Layout/SiteLink.vue";
import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();

const app = createApp(App);

app.component("SiteLink", SiteLink);
app.component("RouterLink", SiteLink);
app.component("router-link", SiteLink);

app.directive("maska", vMaska);
app.use(createPinia());
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

app.mount("#app");
