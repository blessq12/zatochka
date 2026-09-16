import themeTogglerService from "@shared/themeTogglerService.js";

themeTogglerService.init();

const toggle = document.querySelector("[data-mobile-menu-toggle]");
const menu = document.querySelector("[data-mobile-menu]");

if (toggle && menu) {
    toggle.addEventListener("click", () => {
        const open = !menu.classList.contains("hidden");
        menu.classList.toggle("hidden", open);
        toggle.setAttribute("aria-expanded", open ? "false" : "true");
    });
}
