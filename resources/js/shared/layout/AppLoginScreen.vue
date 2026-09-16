<script>
export default {
    name: "AppLoginScreen",
    props: {
        title: { type: String, required: true },
        subtitle: { type: String, default: "" },
        loading: { type: Boolean, default: false },
        error: { type: String, default: "" },
        submitLabel: { type: String, default: "Войти" },
        email: { type: String, default: "" },
        password: { type: String, default: "" },
    },
    emits: ["update:email", "update:password", "submit"],
};
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-dark-blue-500 via-blue-500 to-pink-500 px-4 py-8"
    >
        <form
            class="w-full max-w-md space-y-5 border border-white/25 bg-white/90 p-8 shadow-2xl backdrop-blur-xl sm:p-10"
            @submit.prevent="$emit('submit')"
        >
            <div>
                <h1 class="text-2xl font-jost-bold text-dark-blue-500 sm:text-3xl">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-2 text-sm text-slate-500">
                    {{ subtitle }}
                </p>
            </div>

            <div
                v-if="error"
                class="bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ error }}
            </div>

            <div>
                <label class="mb-2 block text-sm font-jost-medium text-slate-700">Email</label>
                <input
                    :value="email"
                    type="email"
                    required
                    autocomplete="username"
                    class="w-full border border-slate-300 px-3 py-3 text-dark-blue-500 outline-none focus:border-pink-500"
                    @input="$emit('update:email', $event.target.value)"
                />
            </div>

            <div>
                <label class="mb-2 block text-sm font-jost-medium text-slate-700">Пароль</label>
                <input
                    :value="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full border border-slate-300 px-3 py-3 text-dark-blue-500 outline-none focus:border-pink-500"
                    @input="$emit('update:password', $event.target.value)"
                />
            </div>

            <button
                type="submit"
                class="w-full bg-pink-500 py-3 font-jost-bold text-white hover:bg-pink-600 disabled:opacity-50"
                :disabled="loading"
            >
                {{ loading ? "Вход…" : submitLabel }}
            </button>
        </form>
    </div>
</template>
