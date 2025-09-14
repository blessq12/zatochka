<script>
export default {
    name: "Auth",
    data() {
        return {
            currentForm: "login",
        };
    },
};
</script>

<template>
    <div class="flex gap-2">
        <button
            @click="currentForm = 'login'"
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Login
        </button>
        <button
            @click="currentForm = 'register'"
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Register
        </button>
    </div>

    <div v-if="currentForm === 'login'">
        <LoginForm />
    </div>
    <div v-else>
        <RegisterForm />
    </div>
</template>

<style scoped></style>
