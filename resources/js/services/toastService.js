import { useToast } from "vue-toastification";

class ToastService {
    constructor() {
        this.toast = useToast();
    }

    /**
     * Показать успешное уведомление
     */
    success(message, options = {}) {
        return this.toast.success(message, {
            timeout: 4000,
            ...options,
        });
    }

    /**
     * Показать ошибку
     */
    error(message, options = {}) {
        return this.toast.error(message, {
            timeout: 6000,
            ...options,
        });
    }

    /**
     * Показать предупреждение
     */
    warning(message, options = {}) {
        return this.toast.warning(message, {
            timeout: 5000,
            ...options,
        });
    }

    /**
     * Показать информационное сообщение
     */
    info(message, options = {}) {
        return this.toast.info(message, {
            timeout: 4000,
            ...options,
        });
    }

    /**
     * Обновить тост
     */
    update(id, options) {
        return this.toast.update(id, options);
    }

    /**
     * Закрыть тост
     */
    dismiss(id) {
        return this.toast.dismiss(id);
    }

    /**
     * Закрыть все тосты
     */
    clear() {
        return this.toast.clear();
    }

    /**
     * Показать уведомление о сохранении
     */
    saved(options = {}) {
        return this.success("Сохранено успешно!", options);
    }

    /**
     * Показать уведомление об удалении
     */
    deleted(options = {}) {
        return this.success("Удалено успешно!", options);
    }

    /**
     * Показать уведомление о создании
     */
    created(options = {}) {
        return this.success("Создано успешно!", options);
    }

    /**
     * Показать уведомление об обновлении
     */
    updated(options = {}) {
        return this.success("Обновлено успешно!", options);
    }

    /**
     * Показать ошибку валидации
     */
    validationError(message = "Ошибка валидации", options = {}) {
        return this.error(message, options);
    }

    /**
     * Показать ошибку сети
     */
    networkError(options = {}) {
        return this.error("Ошибка сети. Проверьте соединение.", options);
    }

    /**
     * Показать ошибку сервера
     */
    serverError(options = {}) {
        return this.error("Ошибка сервера. Попробуйте позже.", options);
    }
}

// Создаем глобальный экземпляр
const toastService = new ToastService();

// Делаем доступным глобально
window.toastService = toastService;

export default toastService;
