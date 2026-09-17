import { MaskInput } from "maska";
import { nextTick } from "vue";
import { vMaska } from "maska/vue";

export const PHONE_MASK = "+7 (###) ###-##-##";

const boundInputs = new WeakMap();

/**
 * Навешивает маску телефона на input[type=tel] внутри root (или на сам input).
 */
export function bindTelInputs(root = document) {
    if (!root) {
        return;
    }

    const nodes = [];

    if (root instanceof HTMLInputElement && root.type === "tel") {
        nodes.push(root);
    }

    if (typeof root.querySelectorAll === "function") {
        root.querySelectorAll('input[type="tel"]').forEach((el) => {
            nodes.push(el);
        });
    }

    nodes.forEach((el) => {
        if (!(el instanceof HTMLInputElement) || el.type !== "tel") {
            return;
        }

        if (boundInputs.has(el)) {
            return;
        }

        el.setAttribute("data-maska", PHONE_MASK);
        el.setAttribute("inputmode", "tel");
        el.setAttribute("autocomplete", "tel");
        el.setAttribute("placeholder", el.getAttribute("placeholder") || PHONE_MASK);

        boundInputs.set(el, new MaskInput(el, { mask: PHONE_MASK }));
    });
}

/**
 * Vue-плагин: maska-директива + автомаска для всех input[type=tel].
 */
export function installPhoneMask(app) {
    app.directive("maska", vMaska);

    app.mixin({
        mounted() {
            nextTick(() => bindTelInputs(this.$el));
        },
        updated() {
            nextTick(() => bindTelInputs(this.$el));
        },
        beforeUnmount() {
            const root = this.$el;
            if (!root?.querySelectorAll) {
                return;
            }

            const destroy = (el) => {
                const instance = boundInputs.get(el);
                if (instance) {
                    instance.destroy();
                    boundInputs.delete(el);
                }
            };

            if (root instanceof HTMLInputElement) {
                destroy(root);
            }

            root.querySelectorAll?.('input[type="tel"]').forEach(destroy);
        },
    });
}
