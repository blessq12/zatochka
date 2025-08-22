class ModalService {
    constructor() {
        this.modals = new Map();
        this.activeModal = null;
        this.modalStack = [];
    }

    /**
     * Регистрирует модалку в сервисе
     */
    register(id, modalComponent) {
        this.modals.set(id, modalComponent);
    }

    /**
     * Отменяет регистрацию модалки
     */
    unregister(id) {
        this.modals.delete(id);
        this.modalStack = this.modalStack.filter((modal) => modal.id !== id);
    }

    /**
     * Открывает модалку по ID
     */
    open(id, options = {}) {
        const modal = this.modals.get(id);
        if (!modal) {
            console.warn(`Modal with id "${id}" not found`);
            return false;
        }

        // Закрываем предыдущую модалку если нужно
        if (options.closePrevious !== false && this.activeModal) {
            this.close(this.activeModal);
        }

        // Добавляем в стек
        this.modalStack.push({ id, options });
        this.activeModal = id;

        // Открываем модалку
        modal.show = true;

        // Применяем опции
        if (options.title) modal.title = options.title;
        if (options.size) modal.size = options.size;
        if (options.closeOnBackdrop !== undefined)
            modal.closeOnBackdrop = options.closeOnBackdrop;
        if (options.closeOnEscape !== undefined)
            modal.closeOnEscape = options.closeOnEscape;
        if (options.showCloseButton !== undefined)
            modal.showCloseButton = options.showCloseButton;

        return true;
    }

    /**
     * Закрывает модалку по ID
     */
    close(id) {
        const modal = this.modals.get(id);
        if (!modal) {
            console.warn(`Modal with id "${id}" not found`);
            return false;
        }

        modal.show = false;

        // Удаляем из стека
        this.modalStack = this.modalStack.filter((modal) => modal.id !== id);

        // Обновляем активную модалку
        if (this.activeModal === id) {
            this.activeModal =
                this.modalStack.length > 0
                    ? this.modalStack[this.modalStack.length - 1].id
                    : null;
        }

        return true;
    }

    /**
     * Закрывает все модалки
     */
    closeAll() {
        this.modals.forEach((modal, id) => {
            modal.show = false;
        });
        this.modalStack = [];
        this.activeModal = null;
    }

    /**
     * Закрывает активную модалку
     */
    closeActive() {
        if (this.activeModal) {
            this.close(this.activeModal);
        }
    }

    /**
     * Показывает модалку подтверждения
     */
    confirm(options = {}) {
        return new Promise((resolve) => {
            const defaultOptions = {
                title: "Подтверждение",
                message: "Вы уверены, что хотите выполнить это действие?",
                confirmText: "Да",
                cancelText: "Отмена",
                type: "warning", // warning, danger, info
                size: "sm",
            };

            const config = { ...defaultOptions, ...options };

            // Создаем временную модалку подтверждения
            const modalId = `confirm_${Date.now()}`;

            // Здесь нужно создать компонент ConfirmModal
            // Пока что возвращаем промис
            resolve(true);
        });
    }

    /**
     * Показывает модалку уведомления
     */
    alert(options = {}) {
        return new Promise((resolve) => {
            const defaultOptions = {
                title: "Уведомление",
                message: "",
                type: "info", // success, error, warning, info
                size: "sm",
            };

            const config = { ...defaultOptions, ...options };

            // Создаем временную модалку уведомления
            const modalId = `alert_${Date.now()}`;

            // Здесь нужно создать компонент AlertModal
            // Пока что возвращаем промис
            resolve();
        });
    }

    /**
     * Получает активную модалку
     */
    getActiveModal() {
        return this.activeModal;
    }

    /**
     * Проверяет, открыта ли модалка
     */
    isOpen(id) {
        const modal = this.modals.get(id);
        return modal ? modal.show : false;
    }

    /**
     * Проверяет, есть ли открытые модалки
     */
    hasOpenModals() {
        return this.modalStack.length > 0;
    }
}

// Создаем глобальный экземпляр
const modalService = new ModalService();

export default modalService;
