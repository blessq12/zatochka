<script>
import MobileMenu from "./MobileMenu.vue";

const HEADER_CLOSED = "#C20A6C";
const HEADER_OPEN = "#003859";
const LOGO_ON_PINK = "#003859";
const LOGO_ON_BLUE = "#C20A6C";

export default {
    name: "MobileMenuIsland",
    components: {
        MobileMenu,
    },
    props: {
        socialLinks: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            isOpen: false,
        };
    },
    mounted() {
        this.header = document.querySelector("[data-site-header]");
        this.logoIcon = document.querySelector("[data-site-logo-icon]");
        this.logoDot = document.querySelector("[data-site-logo-dot]");
        this.logoLink = document.querySelector("[data-site-logo-link]");
        this.toggleButtons = Array.from(
            document.querySelectorAll("[data-mobile-menu-toggle]")
        );
        this.onToggle = () => {
            this.isOpen = !this.isOpen;
            this.syncUi();
        };
        this.toggleButtons.forEach((button) => {
            button.addEventListener("click", this.onToggle);
        });
        this.syncUi();
    },
    beforeUnmount() {
        this.toggleButtons?.forEach((button) => {
            button.removeEventListener("click", this.onToggle);
        });
        document.body.style.overflow = "";
        this.applyHeaderClosed();
    },
    methods: {
        close() {
            this.isOpen = false;
            this.syncUi();
        },
        syncUi() {
            this.syncToggleButtons();
            this.syncBodyScroll();
            this.syncHeader();
        },
        syncBodyScroll() {
            document.body.style.overflow = this.isOpen ? "hidden" : "";
        },
        syncHeader() {
            if (this.isOpen) {
                this.applyHeaderOpen();
            } else {
                this.applyHeaderClosed();
            }
        },
        applyHeaderOpen() {
            if (this.header) {
                this.header.style.backgroundColor = HEADER_OPEN;
            }
            this.setLogoFill(LOGO_ON_BLUE);
            if (this.logoDot) {
                this.logoDot.classList.remove("text-[#003859]");
                this.logoDot.classList.add("text-[#C20A6C]");
            }
            if (this.logoLink) {
                this.logoLink.classList.remove("focus:ring-offset-[#C20A6C]");
                this.logoLink.classList.add("focus:ring-offset-[#003859]");
            }
        },
        applyHeaderClosed() {
            if (this.header) {
                this.header.style.backgroundColor = "";
            }
            this.setLogoFill(LOGO_ON_PINK);
            if (this.logoDot) {
                this.logoDot.classList.remove("text-[#C20A6C]");
                this.logoDot.classList.add("text-[#003859]");
            }
            if (this.logoLink) {
                this.logoLink.classList.remove("focus:ring-offset-[#003859]");
                this.logoLink.classList.add("focus:ring-offset-[#C20A6C]");
            }
        },
        setLogoFill(color) {
            this.logoIcon?.querySelectorAll("path").forEach((path) => {
                path.setAttribute("fill", color);
            });
        },
        syncToggleButtons() {
            const lineColor = this.isOpen ? "bg-white" : "bg-black";
            this.toggleButtons?.forEach((button) => {
                button.setAttribute(
                    "aria-expanded",
                    this.isOpen ? "true" : "false"
                );
                button.setAttribute(
                    "aria-label",
                    this.isOpen ? "Закрыть меню" : "Меню"
                );
                const spans = button.querySelectorAll("span");
                if (spans.length < 3) {
                    return;
                }

                if (this.isOpen) {
                    spans[0].className = `absolute left-1/2 top-1/2 block w-6 h-0.5 ${lineColor} transition-all duration-300 -translate-x-1/2 -translate-y-1/2 rotate-45`;
                    spans[1].className = `absolute left-1/2 top-1/2 block w-6 h-0.5 ${lineColor} transition-all duration-300 -translate-x-1/2 -translate-y-1/2 opacity-0`;
                    spans[2].className = `absolute left-1/2 top-1/2 block w-6 h-0.5 ${lineColor} transition-all duration-300 -translate-x-1/2 -translate-y-1/2 -rotate-45`;
                } else {
                    spans[0].className = `absolute left-1/2 top-1/2 block w-6 h-0.5 ${lineColor} transition-all duration-300 -translate-x-1/2 -translate-y-[7px]`;
                    spans[1].className = `absolute left-1/2 top-1/2 block w-6 h-0.5 ${lineColor} transition-all duration-300 -translate-x-1/2 -translate-y-1/2`;
                    spans[2].className = `absolute left-1/2 top-1/2 block w-6 h-0.5 ${lineColor} transition-all duration-300 -translate-x-1/2 translate-y-[5px]`;
                }
            });
        },
    },
};
</script>

<template>
    <MobileMenu
        :is-open="isOpen"
        :social-links="socialLinks"
        @close="close"
    />
</template>
