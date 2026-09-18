<script>
import AppLoginScreen from "@shared/layout/AppLoginScreen.vue";
import { mapStores } from "pinia";
import { useManagerStore } from "../stores/managerStore.js";

export default {
    name: "ManagerLoginForm",
    components: { AppLoginScreen },
    data() {
        return {
            form: { email: "", password: "" },
        };
    },
    computed: {
        ...mapStores(useManagerStore),
    },
    methods: {
        async submit() {
            const result = await this.managerStore.login(this.form);
            if (result.success) {
                const redirect = this.$route.query.redirect;
                this.$router.replace(
                    typeof redirect === "string" && redirect.startsWith("/")
                        ? redirect
                        : { name: "manager.dashboard" },
                );
            }
        },
    },
};
</script>

<template>
    <AppLoginScreen
        title="Вход менеджера"
        subtitle="Панель управления Заточка.ТСК"
        :email="form.email"
        :password="form.password"
        :loading="managerStore.isLoading"
        :error="managerStore.error || ''"
        @update:email="form.email = $event"
        @update:password="form.password = $event"
        @submit="submit"
    />
</template>
