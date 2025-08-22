<template>
    <Modal
        :show="show"
        :title="title"
        :size="size"
        :close-on-backdrop="false"
        :close-on-escape="true"
        :show-close-button="false"
        @close="handleCancel"
    >
        <div class="text-center">
            <!-- Иконка -->
            <div
                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4"
                :class="iconClasses"
            >
                <i :class="iconClass" class="text-xl"></i>
            </div>

            <!-- Сообщение -->
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ title }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ message }}
            </p>
        </div>

        <template #footer>
            <button
                @click="handleCancel"
                class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 transition-colors"
            >
                {{ cancelText }}
            </button>
            <button
                @click="handleConfirm"
                class="inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                :class="confirmButtonClasses"
            >
                {{ confirmText }}
            </button>
        </template>
    </Modal>
</template>

<script>
import Modal from "../Modal.vue";

export default {
    name: "ConfirmModal",
    components: {
        Modal,
    },
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: "Подтверждение",
        },
        message: {
            type: String,
            default: "Вы уверены, что хотите выполнить это действие?",
        },
        confirmText: {
            type: String,
            default: "Да",
        },
        cancelText: {
            type: String,
            default: "Отмена",
        },
        type: {
            type: String,
            default: "warning",
            validator: (value) =>
                ["warning", "danger", "info", "success"].includes(value),
        },
        size: {
            type: String,
            default: "sm",
        },
    },
    emits: ["confirm", "cancel", "close"],
    computed: {
        iconClasses() {
            const classes = {
                warning: "bg-yellow-100 dark:bg-yellow-900/20",
                danger: "bg-red-100 dark:bg-red-900/20",
                info: "bg-blue-100 dark:bg-blue-900/20",
                success: "bg-green-100 dark:bg-green-900/20",
            };
            return classes[this.type];
        },
        iconClass() {
            const icons = {
                warning: "mdi mdi-alert text-yellow-600 dark:text-yellow-400",
                danger: "mdi mdi-alert-circle text-red-600 dark:text-red-400",
                info: "mdi mdi-information text-blue-600 dark:text-blue-400",
                success:
                    "mdi mdi-check-circle text-green-600 dark:text-green-400",
            };
            return icons[this.type];
        },
        confirmButtonClasses() {
            const classes = {
                warning:
                    "bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500",
                danger: "bg-red-600 hover:bg-red-700 focus:ring-red-500",
                info: "bg-blue-600 hover:bg-blue-700 focus:ring-blue-500",
                success: "bg-green-600 hover:bg-green-700 focus:ring-green-500",
            };
            return classes[this.type];
        },
    },
    methods: {
        handleConfirm() {
            this.$emit("confirm");
        },
        handleCancel() {
            this.$emit("cancel");
        },
    },
};
</script>
