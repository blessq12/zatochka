<template>
    <Modal
        :show="show"
        :title="title"
        :size="size"
        :close-on-backdrop="true"
        :close-on-escape="true"
        :show-close-button="true"
        @close="handleClose"
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
                @click="handleClose"
                class="inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                :class="buttonClasses"
            >
                {{ buttonText }}
            </button>
        </template>
    </Modal>
</template>

<script>
import Modal from "../Modal.vue";

export default {
    name: "AlertModal",
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
            default: "Уведомление",
        },
        message: {
            type: String,
            default: "",
        },
        type: {
            type: String,
            default: "info",
            validator: (value) =>
                ["success", "error", "warning", "info"].includes(value),
        },
        buttonText: {
            type: String,
            default: "OK",
        },
        size: {
            type: String,
            default: "sm",
        },
    },
    emits: ["close"],
    computed: {
        iconClasses() {
            const classes = {
                success: "bg-green-100 dark:bg-green-900/20",
                error: "bg-red-100 dark:bg-red-900/20",
                warning: "bg-yellow-100 dark:bg-yellow-900/20",
                info: "bg-blue-100 dark:bg-blue-900/20",
            };
            return classes[this.type];
        },
        iconClass() {
            const icons = {
                success:
                    "mdi mdi-check-circle text-green-600 dark:text-green-400",
                error: "mdi mdi-alert-circle text-red-600 dark:text-red-400",
                warning: "mdi mdi-alert text-yellow-600 dark:text-yellow-400",
                info: "mdi mdi-information text-blue-600 dark:text-blue-400",
            };
            return icons[this.type];
        },
        buttonClasses() {
            const classes = {
                success: "bg-green-600 hover:bg-green-700 focus:ring-green-500",
                error: "bg-red-600 hover:bg-red-700 focus:ring-red-500",
                warning:
                    "bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500",
                info: "bg-blue-600 hover:bg-blue-700 focus:ring-blue-500",
            };
            return classes[this.type];
        },
    },
    methods: {
        handleClose() {
            this.$emit("close");
        },
    },
};
</script>
