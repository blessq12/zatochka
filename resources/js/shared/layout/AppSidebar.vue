<script>
import AppNavIcon from "./AppNavIcon.vue";
import BrandMark from "./BrandMark.vue";
import { isNavItemActive } from "./navActive.js";

const STORAGE_KEY = "apps.sidebar.collapsed";

export default {
    name: "AppSidebar",
    components: { AppNavIcon, BrandMark },
    props: {
        tagline: {
            type: String,
            default: "",
        },
        /** @type {{ label: string, icon?: string, to: string|object, match?: string, name?: string }[]} */
        items: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            collapsed: this.readCollapsed(),
        };
    },
    methods: {
        readCollapsed() {
            try {
                return localStorage.getItem(STORAGE_KEY) === "1";
            } catch {
                return false;
            }
        },
        persistCollapsed() {
            try {
                localStorage.setItem(
                    STORAGE_KEY,
                    this.collapsed ? "1" : "0",
                );
            } catch {
                // ignore
            }
        },
        toggleCollapsed() {
            this.collapsed = !this.collapsed;
            this.persistCollapsed();
        },
        isActive(item) {
            return isNavItemActive(item, this.$route);
        },
        iconName(item) {
            return item.icon || "dashboard";
        },
    },
};
</script>

<template>
    <aside
        class="app-sidebar sticky top-0 flex h-dvh shrink-0 flex-col bg-pink-500 text-white transition-[width] duration-200 ease-out"
        :class="collapsed ? 'w-[4.5rem]' : 'w-60'"
        :aria-expanded="collapsed ? 'false' : 'true'"
    >
        <div
            class="border-b border-white/15"
            :class="collapsed ? 'px-2 py-4' : 'px-4 py-4'"
        >
            <div
                v-if="collapsed"
                class="flex justify-center text-white"
                :title="tagline || 'Заточка.ТСК'"
            >
                <svg
                    width="28"
                    height="20"
                    viewBox="0 0 36 25"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <path
                        d="M25.3397 12.1789C25.3397 11.6708 24.9268 11.2578 24.4186 11.2578C23.9105 11.2578 23.4976 11.6708 23.4976 12.1789C23.4976 12.687 23.9105 13.1 24.4186 13.1C24.9268 13.1 25.3397 12.6889 25.3397 12.1789Z"
                        fill="currentColor"
                    />
                    <path
                        d="M32.9363 12.1868C32.9363 12.1868 32.9421 12.183 32.944 12.181C32.9421 12.1791 32.9382 12.1772 32.9363 12.1753C34.5178 11.0221 35.5988 9.27314 35.6558 6.92665C35.5493 2.4487 31.7241 0.147881 27.7847 0.0108596V0.00515036C27.7847 0.00515036 23.3239 -0.24225 20.5264 2.6923L23.1697 4.37272C25.0842 2.77032 27.9579 3.01963 27.9579 3.01963C29.0997 3.09004 32.0952 3.96926 32.2246 6.92474C32.0762 10.3217 27.3775 10.396 26.896 10.396V13.9661C27.3755 13.9661 32.0762 14.0404 32.2246 17.4374C32.0971 20.3909 29.1016 21.2721 27.9579 21.3425C27.9579 21.3425 25.0842 21.5918 23.1697 19.9894L20.5264 21.6698C23.3258 24.6043 27.7847 24.3569 27.7847 24.3569V24.3512C31.7241 24.2142 35.5512 21.9153 35.6577 17.4374C35.6007 15.0909 34.5197 13.3419 32.9382 12.1887L32.9363 12.1868Z"
                        fill="currentColor"
                    />
                    <path
                        d="M13.352 17.2355L10.4098 17.245L18.8043 12.1847H18.7986L18.8043 12.179L10.4098 7.1187L13.352 7.12821L22.0396 10.6679C21.2955 5.2632 16.7052 1.08594 11.0969 1.08594C4.96704 1.08594 0 6.05297 0 12.1809C0 18.3088 4.96704 23.2777 11.0969 23.2777C16.7052 23.2777 21.2955 19.1005 22.0396 13.6957L13.352 17.2355Z"
                        fill="currentColor"
                    />
                </svg>
            </div>
            <BrandMark v-else :tagline="tagline" />
        </div>

        <nav
            class="flex-1 space-y-1 overflow-y-auto py-3"
            :class="collapsed ? 'px-1.5' : 'px-2'"
        >
            <router-link
                v-for="item in items"
                :key="item.label"
                :to="item.to"
                class="flex items-center gap-3 font-jost-medium transition-colors"
                :class="[
                    collapsed
                        ? 'justify-center px-2 py-2.5'
                        : 'px-3 py-2.5 text-sm',
                    isActive(item)
                        ? 'bg-white/20 text-white'
                        : 'text-white hover:bg-white/15',
                ]"
                :title="collapsed ? item.label : undefined"
                :aria-label="item.label"
            >
                <AppNavIcon
                    :name="iconName(item)"
                    :active="isActive(item)"
                    class="!h-5 !w-5"
                />
                <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
            </router-link>
        </nav>

        <div class="border-t border-white/15 p-2">
            <button
                type="button"
                class="flex w-full items-center gap-3 px-3 py-2.5 text-sm font-jost-medium text-white/90 transition-colors hover:bg-white/15"
                :class="collapsed ? 'justify-center px-2' : ''"
                :aria-label="collapsed ? 'Развернуть меню' : 'Свернуть меню'"
                :title="collapsed ? 'Развернуть' : 'Свернуть'"
                @click="toggleCollapsed"
            >
                <svg
                    class="h-5 w-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="
                            collapsed
                                ? 'M13 5l7 7-7 7M5 5l7 7-7 7'
                                : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'
                        "
                    />
                </svg>
                <span v-if="!collapsed">Свернуть</span>
            </button>
        </div>
    </aside>
</template>
