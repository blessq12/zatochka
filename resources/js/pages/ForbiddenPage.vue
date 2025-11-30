<script>
export default {
    name: "ForbiddenPage",
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-dark-blue-500 flex items-center justify-center">
        <div class="text-center px-8 sm:px-12 lg:px-16 xl:px-20">
            <h1
                class="text-6xl sm:text-7xl lg:text-8xl font-jost-bold text-[#C3006B] dark:text-[#C3006B] mb-4"
            >
                403
            </h1>
            <h2
                class="text-2xl sm:text-3xl lg:text-4xl font-jost-bold text-dark-blue-500 dark:text-dark-blue-300 mb-6"
            >
                Доступ запрещен
            </h2>
            <p
                class="text-lg sm:text-xl font-jost-regular text-dark-gray-500 dark:text-gray-200 mb-8"
            >
                У вас нет доступа к этой странице. Пожалуйста, войдите в систему или обратитесь к администратору.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <router-link
                    to="/"
                    class="inline-block bg-[#C3006B] hover:bg-[#C3006B]/90 text-white px-10 py-5 rounded-2xl font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform"
                >
                    Вернуться на главную
                </router-link>
                <router-link
                    to="/client/dashboard"
                    class="inline-block bg-dark-blue-500 hover:bg-dark-blue-600 text-white px-10 py-5 rounded-2xl font-jost-bold text-lg sm:text-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 transform"
                >
                    Войти
                </router-link>
            </div>
        </div>
    </div>
</template>

<style scoped></style>

