<template>
    <div class="telegram-bot-info">
        <!-- Основная карточка -->
        <div
            class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-lg p-6 text-white"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 rounded-full p-3">
                        <i class="mdi mdi-robot text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Telegram Бот</h3>
                        <p class="text-blue-100 text-sm">
                            Получайте уведомления и управляйте заказами
                        </p>
                        <div class="mt-2">
                            <span
                                class="bg-white/20 px-2 py-1 rounded-full text-xs"
                            >
                                @zatochkatsk_bot
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <a
                        href="https://t.me/zatochkatsk_bot"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors duration-200"
                    >
                        <i class="mdi mdi-telegram mr-2"></i>
                        Начать чат
                    </a>
                </div>
            </div>
        </div>

        <!-- Возможности -->
        <div
            class="mt-4 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
        >
            <h4 class="font-medium text-gray-900 dark:text-white mb-3">
                Что умеет бот:
            </h4>
            <div class="space-y-2">
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Уведомления о статусе заказов
                </div>
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Верификация аккаунта
                </div>
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Информация о ценах и услугах
                </div>
                <div
                    class="flex items-center text-sm text-gray-600 dark:text-gray-400"
                >
                    <i class="mdi mdi-check-circle text-green-500 mr-2"></i>
                    Поддержка и консультации
                </div>
            </div>
        </div>

        <!-- Инструкция -->
        <div
            class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
        >
            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                Как подключиться:
            </h4>
            <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <li>1. Нажмите "Начать чат" выше</li>
                <li>
                    2. Отправьте команду
                    <code class="bg-blue-200 dark:bg-blue-800 px-1 rounded"
                        >/start</code
                    >
                </li>
                <li>3. Укажите номер телефона для привязки</li>
                <li>4. Получайте уведомления автоматически</li>
            </ol>
        </div>
    </div>
</template>

<script>
export default {
    name: "TelegramBotInfo",
};
</script>
