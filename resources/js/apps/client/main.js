import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import "./bootstrap";
import App from "./App.vue";
import router from "./router";
import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();

const app = createApp(App);
const pinia = createPinia();

app.directive("maska", vMaska);
app.use(pinia);
app.use(router);

app.mount("#app");
