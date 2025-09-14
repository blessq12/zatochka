<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
    <div
        class="max-w-md w-full space-y-8 p-12 bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:bg-gray-800/80 dark:border-gray-700/20">
        <div>
            <h2 class="mt-6 text-center text-4xl font-extrabold text-gray-900 dark:text-gray-100">
                Вход в систему
            </h2>
            <p class="mt-4 text-center text-lg text-gray-600 dark:text-gray-400">
                Введите ваши учетные данные для входа
            </p>
        </div>

        <form class="mt-8 space-y-8" method="POST" action="{{ route('login.post') }}">
            @csrf

            @if ($errors->any())
                <div
                    class="bg-red-50/80 backdrop-blur-lg border border-red-300/50 text-red-700 px-6 py-4 rounded-2xl dark:bg-red-900/30 dark:border-red-600/50 dark:text-red-400">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label for="email"
                        class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-700/60 dark:border-gray-600/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                        placeholder="Email адрес" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password"
                        class="block text-lg font-medium text-gray-700 mb-3 dark:text-gray-300">Пароль</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="w-full px-6 py-4 bg-white/60 backdrop-blur-md border border-white/20 rounded-2xl shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 dark:bg-gray-700/60 dark:border-gray-600/20 dark:text-gray-100 dark:focus:ring-blue-400/50 dark:focus:border-blue-400/50"
                        placeholder="Пароль">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="w-full bg-blue-600/90 backdrop-blur-md hover:bg-blue-700/90 text-white px-8 py-4 rounded-2xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl dark:bg-blue-500/90 dark:hover:bg-blue-600/90">
                    Войти
                </button>
            </div>
        </form>
    </div>
</body>

</html>
