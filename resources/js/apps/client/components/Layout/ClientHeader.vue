<script>
export default {
    name: "ClientHeader",
    emits: ["logout", "toggle-mobile"],
};
</script>

<template>
    <header class="mb-8 sm:mb-10">
        <div class="mb-6 flex items-center justify-between gap-3">
            <a
                href="/"
                class="inline-flex items-center gap-2 font-jost-medium text-dark-gray-500 transition-colors duration-300 hover:text-[#C3006B] dark:text-gray-200 dark:hover:text-[#C3006B]"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>
                На сайт
            </a>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="hidden bg-[#C3006B] px-6 py-3 font-jost-bold text-base text-white transition hover:bg-[#A8005A] sm:text-lg lg:inline-flex"
                    @click="$emit('logout')"
                >
                    Выход
                </button>
                <button
                    type="button"
                    class="rounded p-2 text-dark-blue-500 hover:bg-white/60 lg:hidden dark:text-dark-blue-300"
                    aria-label="Открыть меню"
                    @click="$emit('toggle-mobile')"
                >
                    <svg
                        class="h-7 w-7"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <div class="mb-4 flex items-end justify-between gap-4">
            <h1
                class="text-3xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 sm:text-4xl lg:text-5xl"
            >
                Личный кабинет
            </h1>
        </div>
        <div class="h-px bg-dark-blue-500/30 dark:bg-dark-gray-200/90" />
    </header>
</template>
