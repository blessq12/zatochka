{{-- Пример использования layout как компонента --}}
<x-layouts.app title="Пример страницы">
    {{-- Дополнительные стили в head --}}
    @push('styles')
        <style>
            .custom-style {
                color: red;
            }
        </style>
    @endpush

    {{-- Дополнительные скрипты в конец body --}}
    @push('scripts')
        <script>
            console.log('Custom script loaded');
        </script>
    @endpush

    {{-- Основной контент страницы --}}
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Пример страницы</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600">
                Это пример использования layout как компонента.
                Весь контент здесь будет вставлен в основной слот.
            </p>

            <div class="mt-4">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Пример кнопки
                </button>
            </div>
        </div>
    </div>
</x-layouts.app>
