<script>
import { gsap } from "gsap";

export default {
    name: "CallbackWidget",
    data() {
        return {
            isOpen: false,
            pulseTl: null,
            phoneDisplay: "+7 (999) 999-99-99",
            phoneTel: "+79999999999",
            telegramUrl: "https://t.me/your_bot",
            email: "info@example.com",
        };
    },
    mounted() {
        this.$nextTick(this.startPulse);
    },
    methods: {
        toggle() {
            this.isOpen ? this.close() : this.open();
        },
        open() {
            this.isOpen = true;
            this.$nextTick(() => {
                const panel = this.$refs.panel;
                if (!panel) return;
                gsap.killTweensOf(panel);
                this.stopPulse();
                gsap.set(panel, { opacity: 0, y: 20, scale: 0.9 });
                gsap.to(panel, {
                    opacity: 1,
                    y: 0,
                    scale: 1,
                    duration: 0.4,
                    ease: "back.out(1.4)",
                });
            });
        },
        close() {
            const panel = this.$refs.panel;
            if (!panel) {
                this.isOpen = false;
                this.startPulse();
                return;
            }
            gsap.killTweensOf(panel);
            gsap.to(panel, {
                opacity: 0,
                y: 16,
                scale: 0.96,
                duration: 0.25,
                ease: "power2.inOut",
                onComplete: () => {
                    this.isOpen = false;
                    this.$nextTick(this.startPulse);
                },
            });
        },
        startPulse() {
            const halo = this.$refs.halo;
            if (!halo) return;
            if (this.pulseTl) {
                this.pulseTl.kill();
                this.pulseTl = null;
            }
            gsap.set(halo, { opacity: 0.55, scale: 1 });
            this.pulseTl = gsap.to(halo, {
                opacity: 0.85,
                scale: 1.25,
                duration: 1.2,
                ease: "sine.inOut",
                yoyo: true,
                repeat: -1,
            });
        },
        stopPulse() {
            const halo = this.$refs.halo;
            if (this.pulseTl) {
                this.pulseTl.kill();
                this.pulseTl = null;
            }
            if (halo) {
                gsap.to(halo, {
                    opacity: 0.0,
                    duration: 0.2,
                    ease: "power1.out",
                });
            }
        },
    },
};
</script>

<template>
    <div class="fixed bottom-8 right-8 z-[60] select-none">
        <div class="relative flex flex-col items-end space-y-4 space-y-reverse">
            <transition name="fade">
                <div
                    v-show="isOpen"
                    ref="panel"
                    class="mb-4 max-w-xs sm:max-w-sm bg-white/85 backdrop-blur-2xl rounded-3xl shadow-2xl p-6 border border-white/25 dark:bg-gray-800/90 dark:backdrop-blur-2xl dark:border-gray-600/30"
                >
                    <div class="flex items-start justify-between mb-4">
                        <h3
                            class="text-xl font-jost-bold text-dark-gray-500 dark:text-gray-100"
                        >
                            Связаться с нами
                        </h3>
                        <button
                            @click="close"
                            aria-label="Закрыть"
                            class="ml-4 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100 transition-all duration-300"
                        >
                            ✖️
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <a :href="`tel:${phoneTel}`" class="group no-underline">
                            <div
                                class="bg-blue-50/80 backdrop-blur-lg rounded-2xl p-4 border border-blue-200/30 dark:bg-blue-900/30 dark:border-blue-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center mr-4"
                                    >
                                        <span class="text-2xl">📞</span>
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-jost-medium text-blue-600 dark:text-blue-300"
                                        >
                                            Телефон
                                        </div>
                                        <div
                                            class="text-lg font-jost-bold text-dark-gray-500 dark:text-gray-100"
                                        >
                                            {{ phoneDisplay }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a
                            :href="telegramUrl"
                            target="_blank"
                            rel="noopener"
                            class="group no-underline"
                        >
                            <div
                                class="bg-pink-50/80 backdrop-blur-lg rounded-2xl p-4 border border-pink-200/30 dark:bg-pink-900/30 dark:border-pink-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 bg-pink-500/20 rounded-2xl flex items-center justify-center mr-4"
                                    >
                                        <span class="text-2xl">🤖</span>
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-jost-medium text-pink-600 dark:text-pink-300"
                                        >
                                            Telegram
                                        </div>
                                        <div
                                            class="text-lg font-jost-bold text-dark-gray-500 dark:text-gray-100 truncate max-w-[200px]"
                                        >
                                            Открыть бота
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a :href="`mailto:${email}`" class="group no-underline">
                            <div
                                class="bg-light-pink-50/80 backdrop-blur-lg rounded-2xl p-4 border border-light-pink-200/30 dark:bg-light-pink-900/30 dark:border-light-pink-800/20 hover:shadow-2xl hover:scale-105 transform transition-all duration-500"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="w-12 h-12 bg-light-pink-400/20 rounded-2xl flex items-center justify-center mr-4"
                                    >
                                        <span class="text-2xl">✉️</span>
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-jost-medium text-light-pink-600 dark:text-light-pink-300"
                                        >
                                            Почта
                                        </div>
                                        <div
                                            class="text-lg font-jost-bold text-dark-gray-500 dark:text-gray-100 truncate max-w-[200px]"
                                        >
                                            {{ email }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </transition>

            <div class="flex items-center gap-4">
                <div class="relative">
                    <div
                        ref="halo"
                        class="absolute inset-0 -m-4 rounded-full bg-gradient-to-r from-blue-500 to-dark-blue-500 blur-2xl opacity-60 pointer-events-none"
                    ></div>
                    <button
                        @click="toggle"
                        aria-label="Открыть контакты"
                        class="relative w-20 h-20 rounded-3xl shadow-2xl border border-white/30 bg-gradient-to-r from-blue-500 to-dark-blue-500 text-white flex items-center justify-center hover:shadow-3xl hover:scale-105 transform transition-all duration-500 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-blue"
                    >
                        <span v-if="!isOpen" class="text-3xl">📞</span>
                        <span v-else class="text-3xl">⬇️</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
