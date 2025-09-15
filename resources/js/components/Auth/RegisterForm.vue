<script>
import { object, string } from "yup";
export default {
    name: "RegisterForm",
    data() {
        return {
            form: object({
                name: string().required(),
                email: string().email().required(),
                password: string().required(),
                telegram: string().required(),
            }),
        };
    },
    methods: {
        handleSubmit() {
            console.log(this.form);
        },
    },
};
</script>

<template>
    <div class="max-w-md mx-auto">
        <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Имя</label
                >
                <input
                    type="text"
                    v-model="form.name"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                />
            </div>

            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Email</label
                >
                <input
                    type="email"
                    v-model="form.email"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                />
            </div>

            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Пароль</label
                >
                <input
                    type="password"
                    v-model="form.password"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                />
            </div>

            <div>
                <label
                    class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300"
                    >Telegram</label
                >
                <input
                    type="text"
                    v-model="form.telegram"
                    class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-800/60 dark:border-gray-700/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                />
            </div>

            <button
                type="submit"
                class="bg-pink-600/90 backdrop-blur-xs hover:bg-pink-700/90 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-pink-500/90 dark:hover:bg-pink-600/90"
            >
                Зарегистрироваться
            </button>
        </form>
    </div>
</template>

<style scoped></style>
