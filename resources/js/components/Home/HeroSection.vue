<script>
export default {
    name: "HeroSection",
    data() {
        return {
            isDark: false,
        };
    },
    computed: {
        logoColor() {
            return this.isDark ? "#C20A6C" : "#003859";
        },
    },
    mounted() {
        this.updateThemeState();
        // Отслеживаем изменения темы
        const observer = new MutationObserver(() => {
            this.updateThemeState();
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
    },
    methods: {
        updateThemeState() {
            this.isDark = document.documentElement.classList.contains("dark");
        },
    },
};
</script>

<template>
    <div
        class="container mx-auto px-8 sm:px-12 lg:px-16 xl:px-20 py-12 sm:py-16 lg:py-20"
    >
        <div class="flex flex-col items-center text-center space-y-4 mb-10">
            <div class="flex flex-col items-center space-y-3">
                <div class="flex flex-col items-center space-y-2">
                    <svg
                        width="72"
                        height="50"
                        viewBox="0 0 36 25"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-20 h-14"
                    >
                        <path
                            d="M25.3397 12.1789C25.3397 11.6708 24.9268 11.2578 24.4186 11.2578C23.9105 11.2578 23.4976 11.6708 23.4976 12.1789C23.4976 12.687 23.9105 13.1 24.4186 13.1C24.9268 13.1 25.3397 12.6889 25.3397 12.1789Z"
                            :fill="logoColor"
                        />
                        <path
                            d="M32.9363 12.1868C32.9363 12.1868 32.9421 12.183 32.944 12.181C32.9421 12.1791 32.9382 12.1772 32.9363 12.1753C34.5178 11.0221 35.5988 9.27314 35.6558 6.92665C35.5493 2.4487 31.7241 0.147881 27.7847 0.0108596V0.00515036C27.7847 0.00515036 23.3239 -0.24225 20.5264 2.6923L23.1697 4.37272C25.0842 2.77032 27.9579 3.01963 27.9579 3.01963C29.0997 3.09004 32.0952 3.96926 32.2246 6.92474C32.0762 10.3217 27.3775 10.396 26.896 10.396V13.9661C27.3755 13.9661 32.0762 14.0404 32.2246 17.4374C32.0971 20.3909 29.1016 21.2721 27.9579 21.3425C27.9579 21.3425 25.0842 21.5918 23.1697 19.9894L20.5264 21.6698C23.3258 24.6043 27.7847 24.3569 27.7847 24.3569V24.3512C31.7241 24.2142 35.5512 21.9153 35.6577 17.4374C35.6007 15.0909 34.5197 13.3419 32.9382 12.1887L32.9363 12.1868Z"
                            :fill="logoColor"
                        />
                        <path
                            d="M13.352 17.2355L10.4098 17.245L18.8043 12.1847H18.7986L18.8043 12.179L10.4098 7.1187L13.352 7.12821L22.0396 10.6679C21.2955 5.2632 16.7052 1.08594 11.0969 1.08594C4.96704 1.08594 0 6.05297 0 12.1809C0 18.3088 4.96704 23.2777 11.0969 23.2777C16.7052 23.2777 21.2955 19.1005 22.0396 13.6957L13.352 17.2355Z"
                            :fill="logoColor"
                        />
                    </svg>
                    <div class="text-center space-y-1">
                        <p
                            class="text-2xl sm:text-3xl font-jost-bold text-[#C20A6C] dark:text-[#C20A6C] tracking-wide"
                        >
                            ЗАТОЧКА.ТСК
                        </p>
                        <p
                            class="text-xs sm:text-sm font-jost-regular text-dark-gray-500 dark:text-gray-300 tracking-wide"
                        >
                            ПОРА ЗАТОЧИТЬ ИНСТРУМЕНТЫ
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="relative border border-dark-blue-500/30 dark:border-dark-gray-200/90 px-6 pt-10 pb-6 sm:px-10 sm:pt-12 sm:pb-8 mb-10 bg-white/80 backdrop-blur-xl dark:bg-dark-blue-500/90 dark:backdrop-blur-xl mt-16"
        >
            <!-- Заголовок-бейдж поверх обводки -->
            <h1
                class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[90%] px-4 sm:px-6 bg-white dark:bg-dark-blue-500/90 font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 text-center head-text"
            >
                ПРОФЕССИОНАЛЬНАЯ ЗАТОЧКА ИНСТРУМЕНТОВ
            </h1>
            <p
                class="text-sm sm:text-base lg:text-lg font-jost-regular text-dark-gray-500 dark:text-gray-200 text-center"
            >
                Заточка маникюрных, парикмахерских, грумерских инструментов и
                ремонт оборудования. Более 5 лет опыта и более 40 000
                восстановленных инструментов.
            </p>
        </div>

        <div class="flex flex-col space-y-4 max-w-md mx-auto">
            <router-link
                to="/sharpening"
                class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 dark:bg-dark-blue-600 dark:hover:bg-dark-blue-700 text-white text-center px-8 py-4 font-jost-bold text-lg tracking-wide transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1"
            >
                ЗАКАЗАТЬ ЗАТОЧКУ
            </router-link>
            <router-link
                to="/repair"
                class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 dark:bg-dark-blue-600 dark:hover:bg-dark-blue-700 text-white text-center px-8 py-4 font-jost-bold text-lg tracking-wide transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1"
            >
                ЗАКАЗАТЬ РЕМОНТ
            </router-link>
            <router-link
                to="/delivery"
                class="w-full bg-pink-500 hover:bg-pink-600 dark:bg-pink-600 dark:hover:bg-pink-700 text-white text-center px-8 py-4 font-jost-bold text-lg tracking-wide transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1"
            >
                ДОСТАВКА
            </router-link>
            <router-link
                to="/contacts"
                class="w-full bg-dark-blue-500 hover:bg-dark-blue-600 dark:bg-dark-blue-600 dark:hover:bg-dark-blue-700 text-white text-center px-8 py-4 font-jost-bold text-lg tracking-wide transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1"
            >
                СВЯЗАТЬСЯ С НАМИ
            </router-link>
        </div>
    </div>
</template>

<style scoped>
.head-text {
    font-size: 12px;
    max-width: 100% !important;
}
@media (min-width: 768px) {
    .head-text {
        font-size: 18px;
        max-width: 80% !important;
    }
}
</style>
