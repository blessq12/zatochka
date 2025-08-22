<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click="handleBackdropClick"
            >
                <!-- Backdrop -->
                <div
                    ref="backdrop"
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                ></div>

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div
                        ref="modal"
                        class="relative w-full max-w-md transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl transition-all"
                        :class="sizeClasses"
                        @click.stop
                    >
                        <!-- Header -->
                        <div
                            v-if="title || $slots.header"
                            ref="header"
                            class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4"
                        >
                            <slot name="header">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white"
                                >
                                    {{ title }}
                                </h3>
                            </slot>
                            <button
                                v-if="showCloseButton"
                                @click="close"
                                class="rounded-md p-1 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors"
                            >
                                <i class="mdi mdi-close text-xl"></i>
                            </button>
                        </div>

                        <!-- Content -->
                        <div ref="content" class="px-6 py-4">
                            <slot></slot>
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="$slots.footer"
                            ref="footer"
                            class="flex items-center justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4"
                        >
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
import { gsap } from "gsap";

export default {
    name: "Modal",
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: "",
        },
        size: {
            type: String,
            default: "md",
            validator: (value) =>
                ["sm", "md", "lg", "xl", "full"].includes(value),
        },
        closeOnBackdrop: {
            type: Boolean,
            default: true,
        },
        closeOnEscape: {
            type: Boolean,
            default: true,
        },
        showCloseButton: {
            type: Boolean,
            default: true,
        },
    },
    emits: ["close", "confirm", "cancel"],
    data() {
        return {
            isAnimating: false,
        };
    },
    computed: {
        sizeClasses() {
            const sizes = {
                sm: "max-w-sm",
                md: "max-w-md",
                lg: "max-w-lg",
                xl: "max-w-xl",
                full: "max-w-full mx-4",
            };
            return sizes[this.size];
        },
    },
    watch: {
        show(newVal) {
            if (newVal) {
                this.openModal();
            } else {
                this.closeModal();
            }
        },
    },
    mounted() {
        if (this.closeOnEscape) {
            document.addEventListener("keydown", this.handleEscape);
        }
    },
    beforeUnmount() {
        if (this.closeOnEscape) {
            document.removeEventListener("keydown", this.handleEscape);
        }
    },
    methods: {
        openModal() {
            this.isAnimating = true;

            // Блокируем скролл body
            document.body.style.overflow = "hidden";

            // Анимация backdrop
            gsap.fromTo(
                this.$refs.backdrop,
                {
                    opacity: 0,
                    backdropFilter: "blur(0px)",
                },
                {
                    opacity: 1,
                    backdropFilter: "blur(4px)",
                    duration: 0.3,
                    ease: "power2.out",
                }
            );

            // Анимация модалки
            const tl = gsap.timeline({
                onComplete: () => {
                    this.isAnimating = false;
                    this.$emit("opened");
                },
            });

            tl.fromTo(
                this.$refs.modal,
                {
                    scale: 0.8,
                    opacity: 0,
                    y: 20,
                },
                {
                    scale: 1,
                    opacity: 1,
                    y: 0,
                    duration: 0.4,
                    ease: "power2.out",
                }
            );

            // Stagger анимация для элементов внутри
            if (this.$refs.header) {
                tl.fromTo(
                    this.$refs.header,
                    {
                        y: -20,
                        opacity: 0,
                    },
                    {
                        y: 0,
                        opacity: 1,
                        duration: 0.3,
                        ease: "power2.out",
                    },
                    "-=0.2"
                );
            }

            if (this.$refs.content) {
                tl.fromTo(
                    this.$refs.content,
                    {
                        y: 20,
                        opacity: 0,
                    },
                    {
                        y: 0,
                        opacity: 1,
                        duration: 0.3,
                        ease: "power2.out",
                    },
                    "-=0.1"
                );
            }

            if (this.$refs.footer) {
                tl.fromTo(
                    this.$refs.footer,
                    {
                        y: 20,
                        opacity: 0,
                    },
                    {
                        y: 0,
                        opacity: 1,
                        duration: 0.3,
                        ease: "power2.out",
                    },
                    "-=0.1"
                );
            }
        },

        closeModal() {
            if (this.isAnimating) return;

            this.isAnimating = true;

            // Разблокируем скролл body
            document.body.style.overflow = "";

            const tl = gsap.timeline({
                onComplete: () => {
                    this.isAnimating = false;
                    this.$emit("closed");
                },
            });

            // Анимация закрытия модалки
            tl.to(this.$refs.modal, {
                scale: 0.8,
                opacity: 0,
                y: 20,
                duration: 0.3,
                ease: "power2.in",
            });

            // Анимация закрытия backdrop
            tl.to(
                this.$refs.backdrop,
                {
                    opacity: 0,
                    backdropFilter: "blur(0px)",
                    duration: 0.2,
                    ease: "power2.in",
                },
                "-=0.3"
            );
        },

        close() {
            if (this.isAnimating) return;
            this.$emit("close");
        },

        handleBackdropClick() {
            if (this.closeOnBackdrop && !this.isAnimating) {
                this.close();
            }
        },

        handleEscape(event) {
            if (event.key === "Escape" && this.show && this.closeOnEscape) {
                this.close();
            }
        },

        confirm() {
            this.$emit("confirm");
        },

        cancel() {
            this.$emit("cancel");
        },
    },
};
</script>

<style scoped>
/* Дополнительные стили для модалки */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
