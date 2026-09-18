import axios from "axios";

async function readBlobError(data) {
    if (!(data instanceof Blob)) {
        return data?.message || null;
    }
    try {
        const text = await data.text();
        const parsed = JSON.parse(text);
        return parsed.message || text || null;
    } catch {
        return null;
    }
}

async function openPdfBlob(response, previewWindow = null) {
    const contentType = String(response.headers?.["content-type"] || "");
    if (
        response.status >= 400 ||
        contentType.includes("application/json") ||
        (response.data instanceof Blob && response.data.type === "application/json")
    ) {
        const message =
            (await readBlobError(response.data)) || "Не удалось сформировать PDF";
        if (previewWindow && !previewWindow.closed) {
            previewWindow.close();
        }
        throw new Error(message);
    }

    const blob =
        response.data instanceof Blob && response.data.type === "application/pdf"
            ? response.data
            : new Blob([response.data], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);

    if (previewWindow && !previewWindow.closed) {
        previewWindow.location.href = url;
    } else {
        const anchor = document.createElement("a");
        anchor.href = url;
        anchor.target = "_blank";
        anchor.rel = "noopener,noreferrer";
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
    }

    window.setTimeout(() => URL.revokeObjectURL(url), 60_000);
}

async function requestPdf(config, previewWindow = null) {
    try {
        const response = await axios({
            ...config,
            responseType: "blob",
            validateStatus: () => true,
        });
        await openPdfBlob(response, previewWindow);
    } catch (error) {
        if (previewWindow && !previewWindow.closed) {
            previewWindow.close();
        }
        if (error instanceof Error && error.message) {
            throw error;
        }
        const message =
            (await readBlobError(error.response?.data)) ||
            error.message ||
            "Не удалось сформировать PDF";
        throw new Error(message);
    }
}

export const documentService = {
    async listTemplates() {
        const { data } = await axios.get("/api/order-document-templates");
        return data;
    },

    async updateTemplate(type, body) {
        const { data } = await axios.put(`/api/order-document-templates/${type}`, {
            body,
        });
        return data;
    },

    async previewTemplate(type, body, orderId = null, previewWindow = null) {
        await requestPdf(
            {
                method: "post",
                url: `/api/order-document-templates/${type}/preview`,
                data: {
                    body,
                    order_id: orderId || undefined,
                },
            },
            previewWindow,
        );
    },

    async openOrderDocument(orderId, type, previewWindow = null) {
        await requestPdf(
            {
                method: "get",
                url: `/api/orders/${orderId}/documents/${type}`,
            },
            previewWindow,
        );
    },
};
