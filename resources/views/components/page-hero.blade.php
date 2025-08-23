@props([
    'title' => '',
    'description' => '',
    'breadcrumbs' => [],
])

<section
    class="relative bg-gradient-to-br from-gray-50 via-white to-accent/5 dark:from-gray-900 dark:via-gray-800 dark:to-accent-light/5 py-20 overflow-hidden">
    <!-- Фоновые элементы -->
    <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-20 h-20 bg-accent/10 rounded-full blur-xl"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-accent-light/10 rounded-full blur-xl"></div>
        <div
            class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-accent/5 rounded-full blur-3xl">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4">
        <!-- Хлебные крошки -->
        @if (!empty($breadcrumbs))
            <nav class="mb-8 text-center" aria-label="Хлебные крошки">
                <ol class="flex items-center justify-center space-x-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}"
                            class="flex items-center text-gray-500 dark:text-gray-400 hover:text-accent dark:hover:text-accent-light transition-colors duration-300 font-medium">
                            <i class="mdi mdi-home mr-1"></i>
                            Главная
                        </a>
                    </li>
                    @foreach ($breadcrumbs as $index => $crumb)
                        <li class="flex items-center">
                            <i class="mdi mdi-chevron-right text-gray-400 mx-2"></i>
                            @if ($index === count($breadcrumbs) - 1)
                                <span class="text-gray-900 dark:text-white font-semibold">{{ $crumb['name'] }}</span>
                            @else
                                <a href="{{ $crumb['href'] }}"
                                    class="text-gray-500 dark:text-gray-400 hover:text-accent dark:hover:text-accent-light transition-colors duration-300 font-medium">
                                    {{ $crumb['name'] }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <!-- Заголовок и описание -->
        <div class="text-center">
            @if ($title)
                <h1
                    class="text-5xl md:text-6xl font-black mb-6 bg-gradient-to-r from-gray-900 via-accent to-pink-600 bg-clip-text text-transparent dark:from-white dark:via-accent-light dark:to-pink-400">
                    {!! $title !!}
                </h1>
            @endif

            @if ($description)
                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
</section>
