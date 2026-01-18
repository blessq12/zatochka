<template>
    <div class="pos-page">
        <!-- Экран загрузки -->
        <div v-if="isCheckingAuth" class="pos-loading-screen">
            <div class="pos-loader">
                <div class="loader-spinner"></div>
                <p class="loader-text">Загрузка...</p>
            </div>
        </div>

        <!-- Экран авторизации -->
        <div v-else-if="!isAuthenticated" class="pos-login-screen">
            <div class="pos-login-container">
                <PosLoginForm @login-success="handleLoginSuccess" />
            </div>
        </div>

        <!-- Основное приложение -->
        <div v-else class="pos-app">
            <!-- Overlay для мобильного меню -->
            <div 
                v-if="isMobileMenuOpen" 
                class="mobile-menu-overlay"
                @click="closeMobileMenu"
            ></div>
            
            <PosSidebar :is-mobile-open="isMobileMenuOpen" @close="closeMobileMenu" />
            <div class="pos-main-content">
                <PosHeader @toggle-mobile-menu="toggleMobileMenu" />
                <router-view />
            </div>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import PosHeader from "../components/Pos/Header.vue";
import PosLoginForm from "../components/Pos/LoginForm.vue";
import PosSidebar from "../components/Pos/Sidebar.vue";
import { usePosStore } from "../stores/posStore.js";

export default {
    name: "PosPage",
    components: {
        PosLoginForm,
        PosSidebar,
        PosHeader,
    },
    setup() {
        const router = useRouter();
        const posStore = usePosStore();

        const isAuthenticated = computed(() => posStore.isAuthenticated);
        const user = computed(() => posStore.user);
        const isCheckingAuth = ref(true);
        const isMobileMenuOpen = ref(false);

        const checkAuth = async () => {
            isCheckingAuth.value = true;
            try {
                await posStore.getMe();
            } finally {
                isCheckingAuth.value = false;
            }
        };

        const handleLoginSuccess = () => {
            router.push({ name: "pos.orders.new" });
        };

        const toggleMobileMenu = () => {
            isMobileMenuOpen.value = !isMobileMenuOpen.value;
        };

        const closeMobileMenu = () => {
            isMobileMenuOpen.value = false;
        };

        // Закрываем меню при смене роута
        router.afterEach(() => {
            closeMobileMenu();
        });

        onMounted(() => {
            checkAuth();
        });

        return {
            isAuthenticated,
            user,
            isCheckingAuth,
            isMobileMenuOpen,
            handleLoginSuccess,
            toggleMobileMenu,
            closeMobileMenu,
        };
    },
};
</script>

<style scoped>
.pos-page {
    min-height: 100vh;
    width: 100%;
}

.pos-login-screen {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #003859 0%, #046490 50%, #c3006b 100%);
    padding: 2rem;
}

.pos-login-container {
    width: 100%;
    max-width: 500px;
}

.pos-app {
    display: flex;
    min-height: 100vh;
    background: #f0f7ff;
}

.pos-main-content {
    flex: 1;
    margin-left: 280px;
    min-height: 100vh;
    padding: 2rem;
    transition: margin-left 0.3s ease;
    width: 0;
    min-width: 0;
    overflow-x: hidden;
    box-sizing: border-box;
}

.mobile-menu-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 998;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .pos-login-screen {
        padding: 1rem;
    }

    .pos-login-container {
        max-width: 100%;
    }

    .pos-main-content {
        margin-left: 0;
        padding: 0.75rem;
        width: 100%;
        max-width: 100vw;
        overflow-x: hidden;
    }

    .mobile-menu-overlay {
        display: block;
    }
}

.pos-loading-screen {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #003859 0%, #046490 50%, #c3006b 100%);
}

.pos-loader {
    text-align: center;
    color: white;
}

.loader-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

.loader-text {
    font-size: 1.125rem;
    font-weight: 500;
    font-family: "Jost", sans-serif;
    margin: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
