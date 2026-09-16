import themeTogglerService from "@shared/themeTogglerService.js";
import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.withCredentials = true;

themeTogglerService.init();

const toggleButtons = document.querySelectorAll("[data-mobile-menu-toggle]");
const menu = document.querySelector("[data-mobile-menu]");

if (menu && toggleButtons.length) {
    toggleButtons.forEach((toggle) => {
        toggle.addEventListener("click", () => {
            const isHidden = menu.classList.contains("hidden");
            menu.classList.toggle("hidden", !isHidden);
            menu.classList.toggle("flex", isHidden);
            menu.classList.toggle("flex-col", isHidden);
            toggleButtons.forEach((btn) =>
                btn.setAttribute("aria-expanded", isHidden ? "true" : "false")
            );
        });
    });
}

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

function buildSharpeningPayload(form) {
    const formData = new FormData(form);
    return {
        full_name: formData.get("name") || "",
        phone: formData.get("phone") || "",
        service_type: "sharpening",
        comment: formData.get("comment")?.toString().trim() || null,
        intake_data: {
            tool_type: formData.get("tool_type") || null,
            tools_count: formData.get("tools_count")
                ? Number(formData.get("tools_count"))
                : null,
            extra_comment: formData.get("comment")?.toString().trim() || null,
        },
        needs_delivery: formData.get("needs_delivery") === "on",
        delivery_address:
            formData.get("needs_delivery") === "on"
                ? formData.get("delivery_address")?.toString() || null
                : null,
    };
}

function buildRepairPayload(form) {
    const formData = new FormData(form);
    return {
        full_name: formData.get("name") || "",
        phone: formData.get("phone") || "",
        service_type: "repair",
        comment: formData.get("comment")?.toString().trim() || null,
        intake_data: {
            equipment_type: formData.get("equipment_type") || null,
            device_name: formData.get("device_name")?.toString().trim() || null,
            problem_description:
                formData.get("problem_description")?.toString().trim() || null,
            urgency_type: formData.get("urgency_type") || "standard",
        },
        needs_delivery: formData.get("needs_delivery") === "on",
        delivery_address:
            formData.get("needs_delivery") === "on"
                ? formData.get("delivery_address")?.toString().trim() || null
                : null,
    };
}

function wireDeliveryToggle(form) {
    const checkbox = form.querySelector('[name="needs_delivery"]');
    const addressBlock = form.querySelector("[data-delivery-address]");
    if (!checkbox || !addressBlock) return;
    const sync = () => addressBlock.classList.toggle("hidden", !checkbox.checked);
    checkbox.addEventListener("change", sync);
    sync();
}

function wirePublicOrderForm(form) {
    const serviceType = form.dataset.serviceType;
    const statusEl = form.querySelector("[data-form-status]");
    const errorEl = form.querySelector("[data-form-error]");
    const submitButton = form.querySelector('[type="submit"]');
    wireDeliveryToggle(form);

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (errorEl) {
            errorEl.textContent = "";
            errorEl.classList.add("hidden");
        }
        if (submitButton) submitButton.disabled = true;
        try {
            const payload =
                serviceType === "repair"
                    ? buildRepairPayload(form)
                    : buildSharpeningPayload(form);
            const response = await axios.post("/api/public/orders", payload, {
                headers: csrfToken ? { "X-CSRF-TOKEN": csrfToken } : {},
            });
            if (statusEl) {
                statusEl.textContent =
                    response.data?.data?.message ||
                    "Заказ создан. Менеджер свяжется с вами.";
                statusEl.classList.remove("hidden");
            }
            form.reset();
            wireDeliveryToggle(form);
        } catch (error) {
            const message =
                error.response?.data?.message || "Ошибка создания заказа";
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove("hidden");
            }
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
}

document
    .querySelectorAll("[data-public-order-form]")
    .forEach((form) => wirePublicOrderForm(form));


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
