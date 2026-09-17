<script>
import AppLoginScreen from "@shared/layout/AppLoginScreen.vue";
import AppShell from "@shared/layout/AppShell.vue";
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { navigationItems } from "../navigation.js";
import { usePosStore } from "../stores/posStore.js";

export default {
    name: "PosPage",
    components: {
        AppLoginScreen,
        AppShell,
    },
    setup() {
        const router = useRouter();
        const posStore = usePosStore();

        const isAuthenticated = computed(() => posStore.isAuthenticated);
        const isCheckingAuth = ref(true);
        const loginForm = ref({ email: "", password: "" });

        const userName = computed(() => posStore.user?.email || "");

        const userEmail = computed(() => posStore.user?.email || "");

        const checkAuth = async () => {
            isCheckingAuth.value = true;
            try {
                await posStore.restoreSession();
            } finally {
                isCheckingAuth.value = false;
            }
        };

        const submitLogin = async () => {
            const result = await posStore.login(loginForm.value);
            if (result.success) {
                router.push({ name: "pos.dashboard" });
            }
        };

        const logout = async () => {
            await posStore.logout();
            router.push({ name: "pos" });
        };

        const pageTitle = computed(() => {
            const name = String(router.currentRoute.value.name || "");
            if (name.includes("dashboard")) return "Дашборд";
            return "POS";
        });

        onMounted(() => {
            checkAuth();
        });

        return {
            isAuthenticated,
            isCheckingAuth,
            loginForm,
            userName,
            userEmail,
            navigationItems,
            pageTitle,
            posStore,
            submitLogin,
            logout,
        };
    },
};
</script>

<template>
    <div class="min-h-screen w-full">
        <div
            v-if="isCheckingAuth"
            class="flex min-h-screen items-center justify-center bg-gradient-to-br from-dark-blue-500 via-blue-500 to-pink-500"
        >
            <div class="text-center text-white">
                <div
                    class="mx-auto mb-4 h-14 w-14 animate-spin border-4 border-white/30 border-t-white"
                />
                <p class="font-jost-medium">Загрузка...</p>
            </div>
        </div>

        <AppLoginScreen
            v-else-if="!isAuthenticated"
            title="Вход мастера"
            subtitle="POS Заточка.ТСК"
            :email="loginForm.email"
            :password="loginForm.password"
            :loading="posStore.isLoading"
            :error="posStore.error || ''"
            @update:email="loginForm.email = $event"
            @update:password="loginForm.password = $event"
            @submit="submitLogin"
        />

        <AppShell
            v-else
            tagline="POS"
            :items="navigationItems"
            :user-name="userName"
            :user-email="userEmail"
            @logout="logout"
        >
            <template #title>{{ pageTitle }}</template>
            <router-view />
        </AppShell>
    </div>
</template>
