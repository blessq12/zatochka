<script>
import { mapStores } from "pinia";
import { useAuthStore } from "../stores/authStore.js";

const fieldClass =
    "w-full max-w-full min-w-0 border border-white/20 bg-white/60 px-4 py-3.5 text-dark-gray-500 shadow-lg outline-none backdrop-blur-md transition-all duration-300 focus:border-[#C20A6C]/50 focus:ring-2 focus:ring-[#C20A6C]/30 dark:border-gray-700/20 dark:bg-gray-800/60 dark:text-gray-200 sm:px-6 sm:py-4";

export default {
    name: "ClientRegisterForm",
    data() {
        return {
            form: {
                email: "",
                password: "",
                password_confirmation: "",
            },
            error: "",
            fieldClass,
        };
    },
    computed: {
        ...mapStores(useAuthStore),
    },
    methods: {
        async submit() {
            this.error = "";
            if (this.form.password !== this.form.password_confirmation) {
                this.error = "Пароли не совпадают";
                return;
            }
            const result = await this.authStore.register(this.form);
            if (result.success) {
                this.$router.replace({ name: "client.orders" });
                return;
            }
            this.error = result.error || "Ошибка регистрации";
        },
    },
};
</script>

<template>
    <div class="flex min-h-0 w-full flex-1 flex-col overflow-hidden bg-white dark:bg-dark-blue-500">
        <header class="shrink-0 bg-[#C20A6C] pt-[env(safe-area-inset-top)]">
            <div class="container mx-auto">
                <div
                    class="flex h-16 items-center justify-between px-4 sm:h-20 sm:px-8 lg:px-12"
                >
                    <a
                        href="/"
                        class="group flex max-h-12 items-center gap-2 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-[#C20A6C]"
                    >
                        <svg
                            width="36"
                            height="25"
                            viewBox="0 0 36 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-8 flex-shrink-0 transition-all duration-300 group-hover:scale-105 sm:h-6 sm:w-9"
                            aria-hidden="true"
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
                        <div class="flex flex-col justify-center gap-0.5 leading-none">
                            <span class="text-[9px] font-jost-regular text-white">
                                ОСНОВАНО 2020
                            </span>
                            <span
                                class="text-sm font-jost-bold uppercase text-white sm:text-base"
                            >
                                Заточка.ТСК
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </header>

        <section
            class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-y-contain py-10 sm:py-14 lg:py-16"
        >
            <div class="mx-auto max-w-2xl px-4 sm:px-8 lg:px-16">
                <div
                    class="mb-8 flex gap-2 border border-white/20 bg-white/60 p-2 backdrop-blur-md dark:border-gray-700/20 dark:bg-gray-800/60 sm:mb-10 sm:gap-4"
                >
                    <router-link
                        :to="{ name: 'client.login' }"
                        class="flex-1 px-4 py-3 text-center font-jost-bold text-base uppercase text-dark-gray-500 transition-all duration-300 hover:bg-white/80 dark:text-gray-200 dark:hover:bg-gray-700/80 sm:px-6 sm:py-4 sm:text-lg"
                    >
                        Вход
                    </router-link>
                    <router-link
                        :to="{ name: 'client.register' }"
                        class="flex-1 bg-[#C20A6C] px-4 py-3 text-center font-jost-bold text-base uppercase text-white shadow-lg sm:px-6 sm:py-4 sm:text-lg"
                    >
                        Регистрация
                    </router-link>
                </div>

                <form
                    class="relative border border-dark-blue-500/30 bg-white/80 p-6 pt-10 shadow-xl backdrop-blur-xl dark:border-dark-gray-200/90 dark:bg-dark-blue-500 sm:p-10 sm:pt-12 lg:p-12"
                    @submit.prevent="submit"
                >
                    <h2
                        class="absolute top-0 left-1/2 max-w-[90%] -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-center text-lg font-jost-bold text-[#C20A6C] sm:px-6 sm:text-xl dark:bg-dark-blue-500"
                    >
                        РЕГИСТРАЦИЯ
                    </h2>

                    <div
                        v-if="error"
                        class="mb-6 border border-red-300/50 bg-red-50/80 px-4 py-3 text-red-700 backdrop-blur-lg dark:border-red-600/50 dark:bg-red-900/30 dark:text-red-400 sm:px-6 sm:py-4"
                    >
                        {{ error }}
                    </div>

                    <div class="mb-5 sm:mb-6">
                        <label
                            class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:mb-3 sm:text-lg"
                        >
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="username"
                            :class="fieldClass"
                        />
                    </div>

                    <div class="mb-5 sm:mb-6">
                        <label
                            class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:mb-3 sm:text-lg"
                        >
                            Пароль
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            :class="fieldClass"
                        />
                    </div>

                    <div class="mb-6 sm:mb-8">
                        <label
                            class="mb-2 block text-base font-jost-medium text-dark-gray-500 dark:text-gray-200 sm:mb-3 sm:text-lg"
                        >
                            Повтор пароля
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            :class="fieldClass"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-[#C20A6C] px-8 py-4 font-jost-bold text-lg uppercase text-white shadow-lg transition-all duration-300 hover:bg-[#a0085a] hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50 sm:px-10 sm:py-5 sm:text-xl"
                        :disabled="authStore.isLoading"
                    >
                        {{
                            authStore.isLoading
                                ? "Регистрация…"
                                : "Зарегистрироваться"
                        }}
                    </button>
                </form>
            </div>
        </section>
    </div>
</template>
