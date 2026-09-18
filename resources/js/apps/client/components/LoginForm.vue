<script>
import AppLoginScreen from "@shared/layout/AppLoginScreen.vue";
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";

export default {
    name: "ClientLoginForm",
    components: { AppLoginScreen },
    data() {
        return {
            form: { email: "", password: "" },
            error: "",
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    methods: {
        async submit() {
            this.error = "";
            const result = await this.authStore.login(this.form);
            if (result.success) {
                const redirect = this.$route.query.redirect;
                this.$router.replace(
                    typeof redirect === "string" && redirect.startsWith("/")
                        ? redirect
                        : { name: "client.orders" },
                );
                return;
            }
            this.error = result.error || "Ошибка входа";
        },
    },
};
</script>

<template>
    <div>
        <AppLoginScreen
            title="Вход клиента"
            subtitle="Личный кабинет Заточка.ТСК"
            :email="form.email"
            :password="form.password"
            :loading="authStore.isLoading"
            :error="error"
            @update:email="form.email = $event"
            @update:password="form.password = $event"
            @submit="submit"
        />
        <p class="-mt-16 mb-8 text-center text-base text-white">
            <router-link
                :to="{ name: 'client.register' }"
                class="underline hover:text-pink-200"
            >
                Нет аккаунта? Регистрация
            </router-link>
        </p>
    </div>
</template>
