<script>
import { usePosStore } from "../../stores/posStore.js";
import { useRoute } from "vue-router";

export default {
    name: "PosSidebar",
    props: {
        isMobileOpen: {
            type: Boolean,
            default: false,
        },
    },
    emits: ["close"],
    setup() {
        const route = useRoute();

        const isActiveSection = (sectionName) => {
            const routeName = route.name || "";
            if (sectionName === "dashboard") {
                return routeName === "pos.dashboard";
            }
            return routeName.startsWith(`pos.${sectionName}`);
        };

        return {
            route,
            isActiveSection,
        };
    },
    computed: {
        posStore() {
            return usePosStore();
        },
        fullName() {
            const user = this.posStore.user;
            if (!user) return "";
            const parts = [user.surname, user.name].filter(Boolean);
            return parts.length > 0 ? parts.join(" ") : user.name || "";
        },
    },
};
</script>

<template>
    <aside
        class="pos-sidebar"
        :class="{ 'mobile-open': isMobileOpen }"
    >
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <svg
                    width="36"
                    height="25"
                    viewBox="0 0 36 25"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    class="logo-icon"
                >
                    <path
                        d="M25.3397 12.1789C25.3397 11.6708 24.9268 11.2578 24.4186 11.2578C23.9105 11.2578 23.4976 11.6708 23.4976 12.1789C23.4976 12.687 23.9105 13.1 24.4186 13.1C24.9268 13.1 25.3397 12.6889 25.3397 12.1789Z"
                        fill="#003859"
                    />
                    <path
                        d="M32.9363 12.1868C32.9363 12.1868 32.9421 12.183 32.944 12.181C32.9421 12.1791 32.9382 12.1772 32.9363 12.1753C34.5178 11.0221 35.5988 9.27314 35.6558 6.92665C35.5493 2.4487 31.7241 0.147881 27.7847 0.0108596V0.00515036C27.7847 0.00515036 23.3239 -0.24225 20.5264 2.6923L23.1697 4.37272C25.0842 2.77032 27.9579 3.01963 27.9579 3.01963C29.0997 3.09004 32.0952 3.96926 32.2246 6.92474C32.0762 10.3217 27.3775 10.396 26.896 10.396V13.9661C27.3755 13.9661 32.0762 14.0404 32.2246 17.4374C32.0971 20.3909 29.1016 21.2721 27.9579 21.3425C27.9579 21.3425 25.0842 21.5918 23.1697 19.9894L20.5264 21.6698C23.3258 24.6043 27.7847 24.3569 27.7847 24.3569V24.3512C31.7241 24.2142 35.5512 21.9153 35.6577 17.4374C35.6007 15.0909 34.5197 13.3419 32.9382 12.1887L32.9363 12.1868Z"
                        fill="#003859"
                    />
                    <path
                        d="M13.352 17.2355L10.4098 17.245L18.8043 12.1847H18.7986L18.8043 12.179L10.4098 7.1187L13.352 7.12821L22.0396 10.6679C21.2955 5.2632 16.7052 1.08594 11.0969 1.08594C4.96704 1.08594 0 6.05297 0 12.1809C0 18.3088 4.96704 23.2777 11.0969 23.2777C16.7052 23.2777 21.2955 19.1005 22.0396 13.6957L13.352 17.2355Z"
                        fill="#003859"
                    />
                </svg>
                <div class="logo-text">
                    <span class="logo-founded">ОСНОВАНО 2020</span>
                    <span class="logo-title">ЗАТОЧКА<span class="logo-dot">.</span>ТСК</span>
                    <span class="logo-tagline">POS</span>
                </div>
            </div>
            <button
                v-if="isMobileOpen"
                type="button"
                @click="$emit('close')"
                class="mobile-close-btn"
                aria-label="Закрыть меню"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">
            <router-link
                :to="{ name: 'pos.dashboard' }"
                class="nav-link"
                :class="{ active: isActiveSection('dashboard') }"
            >
                Дашборд
            </router-link>
            <router-link
                :to="{ name: 'pos.orders.new' }"
                class="nav-link"
                :class="{ active: isActiveSection('orders') }"
            >
                Заказы
            </router-link>
            <router-link
                :to="{ name: 'pos.warehouse.index' }"
                class="nav-link"
                :class="{ active: isActiveSection('warehouse') }"
            >
                Склад
            </router-link>
            <router-link
                :to="{ name: 'pos.settings.profile' }"
                class="nav-link"
                :class="{ active: isActiveSection('settings') }"
            >
                Настройки
            </router-link>
        </nav>
    </aside>
</template>

<style scoped>
.pos-sidebar {
    width: 280px;
    background: #c20a6c; /* как навбар на сайте */
    color: white;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    font-family: "Jost", sans-serif;
    box-shadow: 2px 0 12px rgba(0, 0, 0, 0.15);
    z-index: 999;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 1.25rem 1.25rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.logo-icon {
    width: 36px;
    height: 25px;
    flex-shrink: 0;
}

.logo-text {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.logo-founded {
    font-size: 10px;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.2;
}

.logo-title {
    font-size: 1rem;
    font-weight: 700;
    color: white;
    line-height: 1.2;
}

.logo-dot {
    color: #003859;
}

.logo-tagline {
    font-size: 10px;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mobile-close-btn {
    display: none;
    background: transparent;
    border: none;
    color: white;
    padding: 0.25rem;
    cursor: pointer;
    border-radius: 0;
    transition: background 0.2s;
}

.mobile-close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-link {
    display: block;
    padding: 0.75rem 1rem;
    border-radius: 0;
    font-weight: 500;
    font-size: 0.9375rem;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.9);
    transition: all 0.2s;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.nav-link.active {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    font-weight: 700;
}

@media (max-width: 768px) {
    .pos-sidebar {
        transform: translateX(-100%);
        width: 280px;
    }

    .pos-sidebar.mobile-open {
        transform: translateX(0);
    }

    .mobile-close-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-title {
        font-size: 0.9375rem;
    }

    .logo-founded,
    .logo-tagline {
        font-size: 9px;
    }
}
</style>
