/**
 * @param {string} url
 * @returns {'t' | 'wa' | 'vk' | 'instagram' | string}
 */
export function socialIconKey(url) {
    if (!url) {
        return "";
    }

    try {
        const host = new URL(url).hostname.replace(/^www\./i, "").toLowerCase();
        return host.split(".")[0] || "";
    } catch {
        return "";
    }
}
