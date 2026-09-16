import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import "../public/bootstrap.js";
import SharpeningForm from "../public/components/Forms/SharpeningForm.vue";
import SiteLink from "../public/components/Layout/SiteLink.vue";

const el = document.getElementById("sharpening-form-island");

if (el) {
    const app = createApp(SharpeningForm);
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
    app.mount(el);
}
