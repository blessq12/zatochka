/**
 * Регистрация SW для installable PWA (без офлайн-кэша).
 * @param {{ swUrl: string, scope: string }} options
 */
export function registerPwa({ swUrl, scope }) {
    if (typeof window === "undefined" || !("serviceWorker" in navigator)) {
        return;
    }

    const register = () => {
        navigator.serviceWorker.register(swUrl, { scope }).catch(() => {
            // MVP: тихо игнорируем (http localhost / private network и т.п.)
        });
    };

    if (document.readyState === "complete") {
        register();
        return;
    }

    window.addEventListener("load", register, { once: true });
}
