import { useToast } from "vue-toastification";

export const toastService = {
    success: (message, title = "") => useToast().success(message, { title }),
    error: (message, title = "") => useToast().error(message, { title }),
    info: (message, title = "") => useToast().info(message, { title }),
    warning: (message, title = "") => useToast().warning(message, { title }),
};
