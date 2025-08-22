<script>
import { gsap } from "gsap";

export default {
    name: "MobileMenu",

    props: {
        navigation: {
            type: Array,
            required: true,
        },
        contacts: {
            type: Object,
            required: true,
            default: () => ({
                phone: "",
                email: "",
                address: "",
                schedule: "",
            }),
        },
    },

    data() {
        return {
            isOpen: false,
        };
    },

    methods: {
        toggleMenu() {
            this.isOpen = !this.isOpen;

            if (this.isOpen) {
                gsap.to(this.$refs.menu, {
                    x: 0,
                    duration: 0.5,
                    ease: "power2.out",
                });

                gsap.to(this.$refs.overlay, {
                    opacity: 1,
                    duration: 0.3,
                    ease: "power2.out",
                });

                const links = this.$refs.links.children;
                gsap.from(links, {
                    opacity: 0,
                    x: 50,
                    duration: 0.3,
                    stagger: 0.1,
                    ease: "power2.out",
                });

                const contacts = this.$refs.contacts.children;
                gsap.from(contacts, {
                    opacity: 0,
                    x: 50,
                    duration: 0.3,
                    stagger: 0.1,
                    delay: 0.3,
                    ease: "power2.out",
                });
            } else {
                gsap.to(this.$refs.menu, {
                    x: "100%",
                    duration: 0.5,
                    ease: "power2.in",
                });

                gsap.to(this.$refs.overlay, {
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.in",
                    onComplete: () => {
                        this.isOpen = false;
                    },
                });
            }
        },

        handleNavigation(url) {
            this.toggleMenu();
            window.location.href = url;
        },
    },

    mounted() {
        gsap.set(this.$refs.menu, { x: "100%" });
        gsap.set(this.$refs.overlay, { opacity: 0 });
    },
};
</script>

<template>
    <div>
        <button
            @click="toggleMenu"
            class="mobile-menu-btn md:hidden fixed top-4 right-4 z-50 bg-white dark:bg-gray-800 p-2 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
        >
            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16m-7 6h7"
                ></path>
            </svg>
        </button>

        <div
            ref="menu"
            class="fixed top-0 right-0 h-screen bg-white dark:bg-gray-900 shadow-xl z-40 w-full md:w-[40%] transform"
        >
            <div
                class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700"
            >
                <a href="/" class="block">
                    <img src="/logo.png" alt="Logo" class="nav-logo" />
                </a>
            </div>

            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div ref="links" class="space-y-4">
                    <a
                        v-for="(item, index) in navigation"
                        :key="index"
                        @click="handleNavigation(item.href)"
                        class="block text-lg font-medium hover:text-primary-600 transition-colors cursor-pointer dark:text-white dark:hover:text-accent"
                    >
                        {{ item.name }}
                    </a>
                </div>
            </div>

            <!-- Контакты -->
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 dark:text-white">
                    Контакты
                </h3>
                <div ref="contacts" class="space-y-4">
                    <a
                        v-if="contacts.phone"
                        :href="'tel:' + contacts.phone"
                        class="flex items-center space-x-3 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-accent transition-colors"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                            />
                        </svg>
                        <span>{{ contacts.phone }}</span>
                    </a>
                    <a
                        v-if="contacts.email"
                        :href="'mailto:' + contacts.email"
                        class="flex items-center space-x-3 text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-accent transition-colors"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>
                        <span>{{ contacts.email }}</span>
                    </a>
                    <div
                        v-if="contacts.address"
                        class="flex items-center space-x-3 text-gray-600 dark:text-gray-400"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span>{{ contacts.address }}</span>
                    </div>
                    <div
                        v-if="contacts.schedule"
                        class="flex items-center space-x-3 text-gray-600 dark:text-gray-400"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>{{ contacts.schedule }}</span>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div
                ref="overlay"
                v-show="isOpen"
                class="fixed inset-0 z-30 backdrop-blur-sm bg-slate-900/50"
                @click="toggleMenu"
            ></div>
        </Teleport>
    </div>
</template>

<style scoped>
.mobile-menu-btn:focus {
    outline: none;
}

.nav-logo {
    height: 40px;
    width: auto;
}
</style>
