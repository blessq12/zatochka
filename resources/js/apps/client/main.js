import { createPinia } from "pinia";
import { createApp } from "vue";
import "./bootstrap";
import App from "./App.vue";
import router from "./router";
import { installPhoneMask } from "@shared/phoneMask.js";
import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();

const app = createApp(App);
const pinia = createPinia();

installPhoneMask(app);
app.use(pinia);
app.use(router);

app.mount("#app");
