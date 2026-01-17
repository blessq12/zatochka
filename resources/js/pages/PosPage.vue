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
            <PosSidebar />
            <div class="pos-main-content">
                <PosHeader />
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

        onMounted(() => {
            checkAuth();
        });

        return {
            isAuthenticated,
            user,
            isCheckingAuth,
            handleLoginSuccess,
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
