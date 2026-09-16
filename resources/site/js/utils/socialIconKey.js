/**
 * @param {string} url
 * @returns {'telegram' | 'wa' | 'vk' | 'instagram' | 't' | string}
 */
export function socialIconKey(url) {
    if (!url) {
        return "";
    }

    const raw = String(url).toLowerCase();

    if (/whatsapp|wa\.me/.test(raw)) {
        return "wa";
    }

    if (/instagram/.test(raw)) {
        return "instagram";
    }

    if (/t\.me|telegram/.test(raw)) {
        return "telegram";
    }

    if (/vk\.com|vkontakte/.test(raw)) {
        return "vk";
    }

    try {
        const host = new URL(url).hostname.replace(/^www\./i, "").toLowerCase();
        return host.split(".")[0] || "";
    } catch {
        return "";
    }
}
