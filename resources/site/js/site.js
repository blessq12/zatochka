import themeTogglerService from "@shared/themeTogglerService.js";
import axios from "axios";
import { vMaska } from "maska/vue";
import { createPinia } from "pinia";
import { createApp } from "vue";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import SharpeningForm from "./components/Forms/SharpeningForm.vue";
import RepairForm from "./components/Forms/RepairForm.vue";
import MobileMenuIsland from "./components/Layout/MobileMenuIsland.vue";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

themeTogglerService.init();

const toastOptions = {
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
};

function mountIsland(selector, component, props = {}) {
    const el = document.querySelector(selector);
    if (!el) {
        return null;
    }

    const app = createApp(component, props);
    app.use(createPinia());
    app.directive("maska", vMaska);
    app.use(Toast, toastOptions);
    app.mount(el);

    return app;
}

function readJsonAttr(el, name, fallback) {
    const raw = el?.getAttribute(name);
    if (!raw) {
        return fallback;
    }

    try {
        return JSON.parse(raw);
    } catch {
        return fallback;
    }
}

const sharpeningRoot = document.getElementById("sharpening-form-island");
if (sharpeningRoot) {
    mountIsland("#sharpening-form-island", SharpeningForm, {
        phoneTel: sharpeningRoot.dataset.phoneTel || "",
        writeHref: sharpeningRoot.dataset.writeHref || "",
    });
}

const repairRoot = document.getElementById("repair-form-island");
if (repairRoot) {
    mountIsland("#repair-form-island", RepairForm, {
        phoneTel: repairRoot.dataset.phoneTel || "",
        writeHref: repairRoot.dataset.writeHref || "",
    });
}

const mobileRoot = document.getElementById("mobile-menu-island");
if (mobileRoot) {
    mountIsland("#mobile-menu-island", MobileMenuIsland, {
        socialLinks: readJsonAttr(mobileRoot, "data-social-links", []),
    });
}

document.querySelectorAll("[data-faq-list]").forEach((list) => {
    list.querySelectorAll("[data-faq-toggle]").forEach((toggle) => {
        toggle.addEventListener("click", () => {
            const item = toggle.closest("[data-faq-item]");
            if (!item) return;
            const panel = item.querySelector("[data-faq-panel]");
            const icon = item.querySelector("[data-faq-icon]");
            const willOpen = panel?.classList.contains("hidden");

            list.querySelectorAll("[data-faq-item]").forEach((other) => {
                other.querySelector("[data-faq-panel]")?.classList.add("hidden");
                const otherToggle = other.querySelector("[data-faq-toggle]");
                const otherIcon = other.querySelector("[data-faq-icon]");
                otherToggle?.setAttribute("aria-expanded", "false");
                if (otherIcon) otherIcon.textContent = "+";
            });

            if (willOpen && panel) {
                panel.classList.remove("hidden");
                toggle.setAttribute("aria-expanded", "true");
                if (icon) icon.textContent = "−";
            }
        });
    });
});

document.querySelectorAll("[data-reviews-track]").forEach((track) => {
    const root = track.closest("section") || document;
    const amount = () => Math.max(260, Math.floor(track.clientWidth * 0.85));
    root.querySelector("[data-reviews-prev]")?.addEventListener("click", () => {
        track.scrollBy({ left: -amount(), behavior: "smooth" });
    });
    root.querySelector("[data-reviews-next]")?.addEventListener("click", () => {
        track.scrollBy({ left: amount(), behavior: "smooth" });
    });
});
