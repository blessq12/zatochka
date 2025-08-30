<x-app-layout title="Авторизация в CRM">
    <!-- Hero секция -->
    <section
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center py-12 px-4">
        <div class="max-w-lg mx-auto w-full">
            <!-- Заголовок -->
            <div class="text-center mb-12">
                <div class="w-20 h-20 bg-accent rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="mdi mdi-shield-lock text-white text-4xl"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4">
                    Панель <span class="text-accent">управления</span>
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Войдите в систему управления заказами и клиентами
                </p>
            </div>

            <!-- Форма авторизации -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 md:p-12">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Вход в систему</h2>
                    <p class="text-gray-600 dark:text-gray-400">Введите ваши учетные данные</p>
                </div>

                <form method="POST" action="{{ route('crm.authenticate') }}" class="space-y-6">
                    @csrf

                    <!-- Email поле -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="mdi mdi-email-outline text-accent mr-2"></i>
                            Email адрес
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-accent focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-200"
                            placeholder="your@email.com">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Пароль поле -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="mdi mdi-lock-outline text-accent mr-2"></i>
                            Пароль
                        </label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-accent focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-200"
                            placeholder="••••••••">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Запомнить меня -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 text-accent border-gray-300 rounded focus:ring-accent">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Запомнить меня</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-sm text-accent hover:text-accent/80 transition-colors">
                                Забыли пароль?
                            </a>
                        @endif
                    </div>

                    <!-- Кнопка входа -->
                    <button type="submit"
                        class="w-full bg-accent hover:bg-accent/90 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                        <i class="mdi mdi-login mr-2"></i>
                        Войти в систему
                    </button>
                </form>

                <!-- Дополнительная информация -->
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-center text-sm text-gray-600 dark:text-gray-400">
                        <i class="mdi mdi-information-outline text-accent mr-2"></i>
                        <span>Доступ только для авторизованных пользователей</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
